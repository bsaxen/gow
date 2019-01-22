# =============================================
# File: lib.py
# Author: Benny Saxen
# Date: 2019-01-22
# Description: GOW python library 
# =============================================
import urllib
import urllib2
import time
import datetime

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
# End of file
#===================================================
