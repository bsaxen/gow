# =============================================
# File:        piCamera.py
# Author:      Benny Saxen
# Date:        2018-12-08
# Description: application for running a picamera
# =============================================
from time import sleep
import os
from picamera import PiCamera
import datetime
import urllib
import urllib2
import time

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
#===================================================
def placeOrder( itopic, iaction ):
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

def take_picture(path):
    camera = PiCamera()
    camera.resolution = (1280, 1024)
    camera.capture("temp.jpg")
    camera.close()
    
    
os.system("scp {0} {1}@{2}{3}{4}{5}".format("temp.jpg",
                                              ncis_user,
                                              ncis_url,
                                              ncis_internal_path,
                                              ncis_prefix,
                                              ncis_image_name))
