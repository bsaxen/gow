# =============================================
# File: gowHeaterControl.py
# Author: Benny Saxen
# Date: 2019-02-24
# Description: GOW heater control algorithm
# 90 degrees <=> 1152/4 steps = 288
# =============================================
import math
import urllib
import urllib2
import time
import datetime
from gowLib import *

#=====================================================
class HeaterControl:
   bias    = 0.0
   need    = 1

   temperature_indoor    = 999
   temperature_outdoor   = 999

   temperature_indoor_prev    = 999
   temperature_outdoor_prev   = 999

   timeout_temperature_indoor    = 60
   timeout_temperature_outdoor   = 60
#=====================================================
def heater_model(co,md,hc):
    mintemp = float(co.mintemp)
    maxtemp = float(co.maxtemp)
    minheat = float(co.minheat)
    maxheat = float(co.maxheat)
    x_0 = float(co.x_0)
    y_0 = float(co.y_0)

    y = 999

    coeff1 = (maxheat - y_0)/(mintemp - x_0)
    mconst1 = y_0 - coeff1*x_0
    coeff2 = (y_0 - minheat)/(x_0 - maxtemp)
    mconst2 = minheat - coeff2*maxtemp

    ndi = 0
    if hc.temperature_outdoor == 999:
        message = "No data - temperature_outdoor"
        lib_gowPublishLog(co, message )
        ndi = ndi + 1
    if hc.temperature_indoor == 999:
        message = "No data - temperature_indoor"
        lib_gowPublishLog(co, message )
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
        lib_gowPublishLog(co, message )

    if hc.timeout_temperature_outdoor < 1:
	message = "Old data - temperature_outdoor " + str(hc.timeout_temperature_outdoor)
	old_data= 1
	lib_gowPublishLog(co, message )

    if md.mymode == MODE_OFFLINE:
	if all_data_is_available == 1 and old_data == 0:
	    md.mymode = MODE_ONLINE
            message = 'MODE_ONLINE'
	    lib_gowPublishLog(co, message )

    if md.mymode == MODE_ONLINE:
	if old_data == 1:
	    md.mymode = MODE_OFFLINE
	    message = 'MODE_OFFLINE'
	    lib_gowPublishLog(co, message )

        if md.mystate == STATE_OFF:
            if md.mystop == 0:
                md.mystate = STATE_ON
                message = 'STATE_ON'
                lib_gowPublishLog(co, message )

        if md.mystate == STATE_ON:
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
            if md.mystop == 1:
                y = 999
#========================================================================
    payload  = '{\n'
    payload += '"mintemp" : "' + str(co.mintemp) + '",\n'
    payload += '"maxtemp" : "' + str(co.maxtemp) + '",\n'
    payload += '"minheat" : "' + str(co.minheat) + '",\n'
    payload += '"maxheat" : "' + str(co.maxheat) + '",\n'
    payload += '"x_0" : "' + str(co.x_0) + '",\n'
    payload += '"y_0" : "' + str(co.y_0) + '",\n'
    payload += '"need" : "' + str(hc.need) + '",\n'
    payload += '"target" : "' + str(y) + '",\n'
    payload += '"mode" : "' + str(md.mymode) + '",\n'
    payload += '"state" : "' + str(md.mystate) + '",\n'
    payload += '"errors" : "' + str(md.myerrors) + '",\n'
    payload += '"stop" : "' + str(md.mystop) + '",\n'
    payload += '"bias" : "' + str(hc.bias) + '",\n'
    payload += '"temperature_outdoor" : "' + str(hc.temperature_outdoor) + '",\n'
    payload += '"temperature_indoor" : "' + str(hc.temperature_indoor) + '"\n'
    payload += '}\n'

    print payload
    msg = lib_gowPublishDynamic(co,md,payload)

    if ":" in msg:
		p = msg.split(':')
		#print p[1]
		q = p[1].split(",")
		m = len(q)
		if m == 1:
			if q[0] == 'stopcontrol':
				message = 'Stop control: '
				lib_gowPublishLog(co, message )
				md.mystop = 1
			if q[0] == 'startcontrol':
				message = 'Start control: '
				lib_gowPublishLog(co, message )
				md.mystop = 0
		if m == 2:
			if q[0] == 'bias':
				hc.bias = float(q[1])
				message = 'Bias: ' + str(hc.bias)
				lib_gowPublishLog(co, message )

    return

#===================================================
# Setup
#===================================================
hc = HeaterControl()

confile = "gowheatercontrol.conf"
lib_readConfiguration(confile,co)
lib_gowPublishStatic(co)

md.mymode = MODE_OFFLINE
md.mystate = STATE_OFF
#===================================================
# Loop
#===================================================
while True:
    lib_gowIncreaseCounter(co,md)

    url = 'http://' + co.ds_uri[0] + '/' + co.ds_topic[0] + '/payload.json'
    print url
    hc.temperature_indoor_prev = hc.temperature_indoor
    hc.temperature_indoor = lib_readJsonPayload(url,'temp1')
    print hc.temperature_indoor
    diff  = float(hc.temperature_indoor) - float(hc.temperature_indoor_prev)
    if abs(diff) > 10 and hc.temperature_indoor_prev != 999:
        message = 'Temperature indoor error: cur=' + str(hc.temperature_indoor) + ' prev=' + str(hc.temperature_indoor_prev)
    	#gowPublishLog(co, message )
    	hc.temperature_indoor = hc.temperature_indoor_prev
    	md.myerrors += 1
    hc.timeout_temperature_indoor = 60

    url = 'http://' + co.ds_uri[1] + '/' + co.ds_topic[1] + '/payload.json'
    print url
    hc.temperature_outdoor_prev = hc.temperature_outdoor
    hc.temperature_outdoor = lib_readJsonPayload(url,'temp1')
    print hc.temperature_outdoor
    diff  = float(hc.temperature_outdoor) - float(hc.temperature_outdoor_prev)
    if abs(diff) > 10 and hc.temperature_outdoor_prev != 999:
        message = 'Temperature outdoor error: cur=' + str(hc.temperature_outdoor) + ' prev=' + str(hc.temperature_outdoor_prev)
    	#gowPublishLog(co, message )
    	hc.temperature_outdoor = hc.temperature_outdoor_prev
    	md.myerrors += 1
    hc.timeout_temperature_outdoor = 60

    heater_model(co,md,hc)
    print "sleep: " + str(co.myperiod) + " triggered: " + str(md.mycounter)
    time.sleep(float(co.myperiod))


#===================================================
# End of file
#===================================================
