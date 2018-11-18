# =============================================
# File: http_client.py
# Author: Benny Saxen
# Date: 2018-11-18
# Description:
# =============================================
import urllib
import urllib2
import time
import datetime

#===================================================
# Configuration
#===================================================
conf_gs_url        = 'http://gow.simuino.com/'
conf_server_name   = 'gowServer.php'
conf_period        = 10
conf_hw            = 'python'
conf_n_wrap_around = 999999
#===================================================
# Topics
#===================================================
topic1 = 'test/temperature/outdoor/0'
topic2 = 'test/temperature/outdoor/1'
#===================================================
def http_get_value( itopic, itype, ivalue, iunit, n, iperiod, ihw ):
#===================================================
	#url = 'http://gow.simuino.com/gowServer.php'
	url = conf_gs_url
	server = conf_server_name
	data = {}
	data['topic'] = itopic
	data['no'] = n
	data['type'] = itype
	data['value'] = ivalue
	data['unit'] = iunit
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
	print the_page
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
	print the_action
#===================================================
n = 0
while True:
	n += 1
	if n > conf_n_wrap_around:
		n = 0
	value = n
	# Set an action for topic1
	http_get_action(topic1,'please do nothing')
	# Send data to topic2
	http_get_value(topic2,'TEMPERATURE', value, 'celcius',n,conf_period,conf_hw)
	time.sleep(conf_period)

#===================================================
# End of file
#===================================================
