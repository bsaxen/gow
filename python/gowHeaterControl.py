# =============================================
# File: gowHeaterControl.py
# Author: Benny Saxen
# Date: 2019-02-17
# Description: IOANT heater control algorithm
# Next Generation
# 90 degrees <=> 1152/4 steps = 288
# =============================================
import math
import urllib
import urllib2
import time
import datetime
from gowLib import *

#======================================
class HeaterControl:
   bias    = 0.0
   need    = 1

   temperature_indoor    = 999
   temperature_outdoor   = 999

   temperature_indoor_prev    = 999
   temperature_outdoor_prev   = 999

   timeout_temperature_indoor    = 60
   timeout_temperature_outdoor   = 60

#======================================

#===================================================
def gowPublishStatic(co):
#===================================================
	domain = co.c_url
	server = co.c_server_app
	data = {}
	# meta data
	data['do']       = 'stat'
	data['desc']     = co.c_desc
	data['tags']     = co.c_tags
	data['topic']    = co.c_topic1
	data['wrap']     = co.c_wrap
	data['period']   = co.c_period
	data['platform'] = 'python'
	data['url']      = domain
  	data['ssid']     = 'nowifi'
	data['action']   = 2

	values = urllib.urlencode(data)
	req = 'http://' + domain + '/' + server + '?' + values
	print req
	try:
		response = urllib2.urlopen(req)
		the_page = response.read()
		print 'Message to ' + co.c_topic1 + ': ' + the_page
		#evaluateAction(the_page)
	except urllib2.URLError as e:
		print e.reason
#===================================================
def gowPublishDynamic(co,cu,payload):
#===================================================
	msg = '-'
	domain = co.c_url
	server = co.c_server_app
	data = {}
	# meta data
	data['do']       = 'dyn'
	data['topic']    = co.c_topic1
	data['no']       = cu.r_counter
	data['rssi']     = 0
	data['dev_ts']   = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	data['fail']     = 0
	data['payload']    = payload

	values = urllib.urlencode(data)
	req = 'http://' + domain + '/' + server + '?' + values
	print req
	try:
		response = urllib2.urlopen(req)
		msg = response.read()
		print 'Message to ' + co.c_topic1 + ': ' + msg
	except urllib2.URLError as e:
		print e.reason

	return msg
#===================================================
def gowPublishLog(co, message ):
#===================================================
	msg = '-'
	domain = co.c_url
	server = co.c_server_app
	data = {}

	data['do']       = 'log'
	data['topic']    = co.c_topic1
	data['dev_ts']   = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	data['log']      = message

	values = urllib.urlencode(data)
	req = 'http://' + domain + '/' + server + '?' + values
	print req
	try:
		response = urllib2.urlopen(req)
		msg = response.read()
		print 'Message to ' + co.c_topic1 + ': ' + msg
		#evaluateAction(the_page)
	except urllib2.URLError as e:
		print e.reason

	return msg

#=====================================================
def gowPublishTarget(value):
    msg = "Publish target temperature message: "+str(value)
    print msg

#=====================================================
def heater_model(co,cu,hc):
    mintemp = float(co.c_mintemp)
    maxtemp = float(co.c_maxtemp)
    minheat = float(co.c_minheat)
    maxheat = float(co.c_maxheat)
    x_0 = float(co.c_x_0)
    y_0 = float(co.c_y_0)

    y = 999

    coeff1 = (maxheat - y_0)/(mintemp - x_0)
    mconst1 = y_0 - coeff1*x_0
    coeff2 = (y_0 - minheat)/(x_0 - maxtemp)
    mconst2 = minheat - coeff2*maxtemp

    ndi = 0
    if hc.temperature_outdoor == 999:
        message = "No data - temperature_outdoor"
        gowPublishLog(co, message )
        ndi = ndi + 1
    if hc.temperature_indoor == 999:
        message = "No data - temperature_indoor"
        gowPublishLog(co, message )
        ndi = ndi + 1

    if ndi > 0:
        print ndi
    if ndi == 0:
        all_data_is_available = 1
    else:
        all_data_is_available = 0

    old_data = 0

    print ndi

    hc.timeout_temperature_indoor -= 1
    hc.timeout_temperature_outdoor -= 1

    if hc.timeout_temperature_indoor < 1:
	message = "Old data - temperature_indoor " + str(hc.timeout_temperature_indoor)
	old_data= 1
        gowPublishLog(co, message )

    if hc.timeout_temperature_outdoor < 1:
	message = "Old data - temperature_outdoor " + str(hc.timeout_temperature_outdoor)
	old_data= 1
	gowPublishLog(co, message )

    if cu.r_mode == MODE_OFFLINE:
	if all_data_is_available == 1 and old_data == 0:
	    cu.r_mode = MODE_ONLINE
            message = 'MODE_ONLINE'
	    gowPublishLog(co, message )

    if cu.r_mode == MODE_ONLINE:
	if old_data == 1:
	    cu.r_mode = MODE_OFFLINE
	    message = 'MODE_OFFLINE'
	    gowPublishLog(co, message )

        if cu.r_state == STATE_OFF:
            if cu.r_stop == 0:
                cu.r_state = STATE_ON
                message = 'STATE_ON'
                gowPublishLog(co, message )

        if cu.r_state == STATE_ON:
            hc.need = 1
            if hc.temperature_indoor > 20:
		hc.need = 0

            temp = hc.temperature_outdoor

            if temp > maxtemp:
                temp = maxtemp
            if temp < mintemp:
                temp = mintemp

            if temp < x_0:
                y = coeff1*temp + mconst1
            else:
                y = coeff2*temp + mconst2

            y = y +hc.bias
            if cu.r_stop == 1:
                y = 999
#========================================================================
    payload  = '{\n'
    payload += '"mintemp" : "' + str(co.c_mintemp) + '",\n'
    payload += '"maxtemp" : "' + str(co.c_maxtemp) + '",\n'
    payload += '"minheat" : "' + str(co.c_minheat) + '",\n'
    payload += '"maxheat" : "' + str(co.c_maxheat) + '",\n'
    payload += '"x_0" : "' + str(co.c_x_0) + '",\n'
    payload += '"y_0" : "' + str(co.c_y_0) + '",\n'
    payload += '"need" : "' + str(hc.need) + '",\n'
    payload += '"target" : "' + str(y) + '",\n'
    payload += '"mode" : "' + str(cu.r_mode) + '",\n'
    payload += '"state" : "' + str(cu.r_state) + '",\n'
    payload += '"errors" : "' + str(cu.r_errors) + '",\n'
    payload += '"stop" : "' + str(cu.r_stop) + '",\n'
    payload += '"bias" : "' + str(hc.bias) + '",\n'
    payload += '"temperature_outdoor" : "' + str(hc.temperature_outdoor) + '",\n'
    payload += '"temperature_indoor" : "' + str(hc.temperature_indoor) + '"\n'
    payload += '}\n'

    print payload
    msg = gowPublishDynamic(co,cu,payload)

    if ":" in msg:
		p = msg.split(':')
		#print p[1]
		q = p[1].split(",")
		m = len(q)
		if m == 1:
			if q[0] == 'stopcontrol':
				message = 'Stop control: '
				gowPublishLog(co, message )
				cu.r_stop = 1
			if q[0] == 'startcontrol':
				message = 'Start control: '
				gowPublishLog(co, message )
				cu.r_stop = 0
		if m == 2:
			if q[0] == 'bias':
				hc.bias = float(q[1])
				message = 'Bias: ' + str(hc.bias)
				gowPublishLog(co, message )

    return

#===================================================
# Setup
#===================================================
co = Configuration()
hc = HeaterControl()
cu = CommonUse()
ds = Datastream()

confile = "gowheatercontrol.conf"
print "Read configuration"
lib_readConfiguration(confile,co)
gowPublishStatic(co)

cu.r_mode = MODE_OFFLINE
cu.r_state = STATE_OFF
#===================================================
# Loop
#===================================================
while True:
    cu.r_counter += 1
    if cu.r_counter > co.c_wrap:
        cu.r_counter = 1

    url = 'http://' + co.c_ds_uri[0] + '/' + co.c_ds_topic[0] + '/payload.json'
    print url
    hc.temperature_indoor_prev = hc.temperature_indoor
    hc.temperature_indoor = lib_readJsonPayload(url,'temp1')
    print hc.temperature_indoor
    diff  = float(hc.temperature_indoor) - float(hc.temperature_indoor_prev)
    if abs(diff) > 10 and hc.temperature_indoor_prev != 999:
        message = 'Temperature indoor error: cur=' + str(hc.temperature_indoor) + ' prev=' + str(hc.temperature_indoor_prev)
    	#gowPublishLog(co, message )
    	hc.temperature_indoor = hc.temperature_indoor_prev
    	cu.r_errors += 1
    hc.timeout_temperature_indoor = 60

    url = 'http://' + co.c_ds_uri[1] + '/' + co.c_ds_topic[1] + '/payload.json'
    print url
    hc.temperature_outdoor_prev = hc.temperature_outdoor
    hc.temperature_outdoor = lib_readJsonPayload(url,'temp1')
    print hc.temperature_outdoor
    diff  = float(hc.temperature_outdoor) - float(hc.temperature_outdoor_prev)
    if abs(diff) > 10 and hc.temperature_outdoor_prev != 999:
        message = 'Temperature outdoor error: cur=' + str(hc.temperature_outdoor) + ' prev=' + str(hc.temperature_outdoor_prev)
    	#gowPublishLog(co, message )
    	hc.temperature_outdoor = hc.temperature_outdoor_prev
    	cu.r_errors += 1
    hc.timeout_temperature_outdoor = 60

    heater_model(co,cu,hc)
    print "sleep: " + str(co.c_period) + " triggered: " + str(cu.r_counter)
    time.sleep(float(co.c_period))


#===================================================
# End of file
#===================================================
