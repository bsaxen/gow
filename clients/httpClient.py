# =============================================
# File: http_client.py
# Author: Benny Saxen
# Date: 2018-11-13
# Description: 
# =============================================
import urllib
import urllib2
import time
import datetime

#===================================================
def http_get( label, typ, value ):
#===================================================
	url = 'http://gow.simuino.com/gowServer.php'
	data = {}
	data['no'] = 1
	data['label'] = label
	data['type'] = typ
	data['value'] = value
	data['datetime'] = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")

	values = urllib.urlencode(data)
	req = url + '?' + values
	try: response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason
	the_page = response.read()
#===================================================
http_get('IoTsax','test', 16)

#===================================================
# End of file
#===================================================
