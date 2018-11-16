# =============================================
# File: http_client.py
# Author: Benny Saxen
# Date: 2018-11-16
# Description:
# =============================================
import urllib
import urllib2
import time
import datetime

#===================================================
# Configuration
#===================================================
period = 10
hw = 'python'

#===================================================
def http_get_value( itopic, itype, ivalue, iunit, n, iperiod, ihw ):
#===================================================
	#url = 'http://gow.simuino.com/gowServer.php'
	url = 'http://127.0.0.1/git/gow/'
	server = 'gowServer.php'
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
	#url = 'http://gow.simuino.com/gowServer.php'
	url = 'http://127.0.0.1/git/gow/'
	server = 'gowServer.php'
	data = {}
	data['topic'] = itopic
	data['action'] = iaction

	values = urllib.urlencode(data)
	req = url + server + '?' + values
	try: response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason
	the_page = response.read()
	print the_page
#===================================================
n = 0
while True:
	n += 1
	if n > 999999:
		n = 0
	value = n
	http_get_action('kvv32/temperature/outdoor/0','please do nothing')
	http_get_value('kvv32/temperature/outdoor/0','TEMPERATURE', value, 'celcius',n,period,hw)
	time.sleep(period)

#===================================================
# End of file
#===================================================
