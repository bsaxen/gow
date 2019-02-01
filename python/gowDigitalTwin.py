# =============================================
# File: gowDigitalTwin.py
# Author: Benny Saxen
# Date: 2018-12-21
# Description:
# =============================================
import urllib
import urllib2
import time
import datetime

#===================================================
# Configuration
#===================================================
#conf_gs_url        = 'http://gow.simuino.com/'
conf_gs_url        = 'http://127.0.0.1/git/gow/'
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
#===================================================
def publishData( itopic, itype, ivalue, iunit, n, iperiod, ihw ):
#===================================================
	#url = 'http://gow.simuino.com/gowServer.php'
	url = conf_gs_url
	server = conf_server_name
	data = {}
	# meta data
	data['do']     = 'data'
	data['topic']  = itopic
	data['no']     = n
	data['wrap']   = conf_wrap
	data['type']   = itype
	data['ts']     = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	data['period'] = iperiod
	data['url']    = url
	data['hw']     = ihw
	data['hash']   = 'nohash'
	# payload
	data['p1'] = 'value'
	data['v1'] = ivalue
	data['p2'] = 'unit'
	data['v2'] = iunit
	
	values = urllib.urlencode(data)
	req = url + server + '?' + values
	try: response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason
	the_page = response.read()
	print 'Message to ' + itopic + ': ' + the_page
	evaluateAction(the_page)
#===================================================
def placeOrder( itopic, iaction ):
#===================================================
	url = conf_gs_url
	server = conf_server_name
	data = {}
	data['do']     = 'action'
	data['topic']  = itopic
	data['order']  = iaction
	values = urllib.urlencode(data)
	req = url + server + '?' + values
	try: response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason
	the_action = response.read()
#===================================================
n = 0
while True:
	n += 1
	if n > conf_wrap:
		n = 0
	value = n
	# Set an action for topic1
	#placeOrder(topic1,'please do nothing')
	# Send data to topic2
	publishData(topic1,'TEMPERATURE', value, 'celcius',n,conf_period,conf_hw)
	publishData(topic2,'TEMPERATURE', value, 'celcius',n,conf_period,conf_hw)
	publishData(topic3,'ELECTRICITY', value, 'watt',n,conf_period,conf_hw)
	print 'sleep ' + str(conf_period) + ' sec'
	time.sleep(conf_period)

#===================================================
# End of file
#===================================================
