# =============================================
# File: httpClient.py
# Author: Benny Saxen
# Date: 2018-11-21
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
#===================================================
# Topics
#===================================================
topic1 = 'test/temperature/outdoor/1'
topic2 = 'test/temperature/outdoor/2'
topic3 = 'test/electricity/house/0'
#===================================================
def http_get_value( itopic, itype, ivalue, iunit, n, iperiod, ihw ):
#===================================================
	#url = 'http://gow.simuino.com/gowServer.php'
	url = conf_gs_url
	server = conf_server_name
	data = {}
	data['do'] = 'data'
	data['topic'] = itopic
	data['no'] = n
	data['wrap'] = conf_wrap
	data['type'] = itype
	data['p1'] = 'value'
	data['v1'] = ivalue
	data['p2'] = 'unit'
	data['v2'] = iunit
	data['ts'] = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	data['period'] = iperiod
	data['url'] = url
	data['hw'] = ihw
	values = urllib.urlencode(data)
	req = url + server + '?' + values
	try: response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason
	the_page = response.read()
	print 'Message to ' + itopic + ': ' + the_page
#===================================================
def http_get_action( itopic, iaction ):
#===================================================
	url = conf_gs_url
	server = conf_server_name
	data = {}
	data['topic'] = itopic
	data['action'] = iaction
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
	#http_get_action(topic1,'please do nothing')
	# Send data to topic2
	http_get_value(topic1,'TEMPERATURE', value, 'celcius',n,conf_period,conf_hw)
	http_get_value(topic2,'TEMPERATURE', value, 'celcius',n,conf_period,conf_hw)
	http_get_value(topic3,'ELECTRICITY', value, 'watt',n,conf_period,conf_hw)
	print 'sleep ' + str(conf_period) + ' sec'
	time.sleep(conf_period)

#===================================================
# End of file
#===================================================
