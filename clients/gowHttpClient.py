# =============================================
# File: gowHttpClient.py
# Author: Benny Saxen
# Date: 2018-12-29
# Description:
# =============================================
import urllib
import urllib2
import time
import datetime

#===================================================
# Configuration
#===================================================
conf_gs_url        = 'gow.simuino.com'
conf_server_name   = 'gowServer.php'
conf_period        = 10
conf_hw            = 'python'
conf_wrap          = 999999
conf_security      = 0 # 0 = no security
conf_secret        = 'my secret'
#===================================================
# Topics
#===================================================
topic1 = 'test/temperature/outdoor/1'
topic2 = 'test/temperature/outdoor/2'
topic3 = 'test/electricity/house/0'
#===================================================
def evaluateAction( action):
	print action	
#===================================================
def readConfiguration():
	try:
		fh = open('configuration.txt', 'r') 
		for line in fh: 
			print line
			word = line.split()
			if word[0] == 'gs_url':
				conf_gs_url      = word[1]
			if word[0] == 'gs_server':
				conf_server_name = word[1]
			if word[0] == 'gs_period':
				conf_period      = word[1]
			if word[0] == 'gs_hw':
				conf_hw          = word[1]
			if word[0] == 'gs_wrap':
				conf_wrap        = word[1]
			if word[0] == 'gs_security':
				conf_security    = word[1]
			if word[0] == 'gs_secret':
				conf_secret      = word[1]
		fh.close()
	except:
		fh = open('configuration.txt', 'w')
		fh.write('gs_url      gow.simuino.com\n')
		fh.write('gs_server   gowServer.php\n')
		fh.write('gs_period   10\n')
		fh.write('gs_hw       python\n')
		fh.write('gs_wrap     999999\n')
		fh.write('gs_security 1\n')
		fh.write('gs_secret   mysecret\n')
		fh.close()
	return
#===================================================
def publishData( itopic, ipayload, n, iperiod, ihw ):
#===================================================
	url = conf_gs_url
	server = conf_server_name
	data = {}
	# meta data
	data['do']     = 'data'
	data['topic']  = itopic
	data['no']     = n
	data['wrap']   = conf_wrap
	data['ts']     = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	data['period'] = iperiod
	data['url']    = url
	data['hw']     = ihw
	data['hash']   = 'nohash'
	# payload
	data['payload'] = ipayload
	
	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values
	print req
	try: 
		response = urllib2.urlopen(req)
		the_page = response.read()
		print 'Message to ' + itopic + ': ' + the_page
		evaluateAction(the_page)
	except urllib2.URLError as e:
		print e.reason

#===================================================
def placeOrder( itopic, iaction, itag ):
#===================================================
	url = conf_gs_url
	server = conf_server_name
	data = {}
	data['do']     = 'action'
	data['topic']  = itopic
	data['order']  = iaction
	data['tag']    = itag
	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values
	print req
	try: 
		response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason
	
#===================================================
readConfiguration()
n = 0
while True:
	n += 1
	if n > conf_wrap:
		n = 0
	value = n
	# Set an action for topic1
	#placeOrder(topic1,'please do nothing','mytag')
	# Send data to topic2
	payload = '{ "value": "' + str(value) + ', "unit": "celsius"}'
	print payload
	publishData(topic1, payload,n,conf_period,conf_hw)
	
	payload = '{ "value": "' + str(value) + ', "unit": "celsius"}'
	print payload
	publishData(topic2, value,n,conf_period,conf_hw)
	
	payload = '{ "value": "' + str(value) + ', "unit": "watt"}'
	print payload
	publishData(topic3, value ,n,conf_period,conf_hw)
	
	print 'sleep ' + str(conf_period) + ' sec'
	time.sleep(conf_period)

#===================================================
# End of file
#===================================================
