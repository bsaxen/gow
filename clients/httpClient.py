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

#===================================================
def http_get_value( itopic, itype, ivalue, iunit, n, period ):
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
	data['period'] = period
	data['url'] = url
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
	http_get_action('kvv32/temperature/outdoor/0','stop readings')
	http_get_value('kvv32/temperature/outdoor/0','TEMPERATURE', 16.1, 'celcius',n,period)
	http_get_action('kvv32/temperature/outdoor/1','start readings')
	http_get_value('kvv32/temperature/outdoor/1','TEMPERATURE', 16.2, 'celcius',n,period)
	http_get_value('kvv32/temperature/outdoor/1','TEMPERATURE', 16.3, 'celcius',n,period)
	time.sleep(period)

#===================================================
# End of file
#===================================================
