# =============================================
# File: gowHeaterTwin.py
# Author: Benny Saxen
# Date: 2019-02-17
# Description: IDigital Twin of Pellets Heater
# 
# 90 degrees <=> 1152/4 steps = 288
# =============================================
import math
import urllib
import urllib2
import time
import datetime
from gowLib import *

#======================================
class Twin:
   r_inertia = 0
   r_onoff   = 0
   r_target  = 999
	

   temperature_water_in  = 0.0
   temperature_water_out = 0.0
   temperature_smoke     = 0.0

   temperature_water_in_prev  = 0.0
   temperature_water_out_prev = 0.0
   temperature_smoke_prev     = 0.0

   timeout_temperature_water_in  = 60
   timeout_temperature_water_out = 60
   timeout_temperature_smoke     = 60

#======================================
tw = Twin()
co = Configuration()
cu = CommonUse()


#=====================================================
def heater_model(p1):	

	if p1.r_onoff < p1.g_onoff:
		publishOnOff(p1.r_onoff)

	CLOCKWISE = 0 # decrease
	COUNTERCLOCKWISE = 1 # increase

	y = 999
	energy = 999
	steps = 999
	all_data_is_new = 0
	old_data = 0
	action = 0

	# If necessary data not available: do nothing
	ndi = 0

	if p1.temperature_water_out == 999:
		message = "No data - temperature_water_out"
		write_log(message)
		#write_history(message)
		ndi = ndi + 1

	if p1.temperature_water_in == 999:
		message = "No data - temperature_water_in"
		write_log(message)
		#write_history(message)
		ndi = ndi + 1

	if p1.temperature_smoke == 999:
		message = "No data - temperature_smoke"
		write_log(message)
		#write_history(message)
		ndi = ndi + 1

	if ndi > 0:
		print ndi
	if ndi == 0:
		all_data_is_available = 1
	else:
		all_data_is_available = 0

	old_data = 0

	p1.timeout_temperature_water_in -= 1
	p1.timeout_temperature_water_out -= 1
	p1.timeout_temperature_smoke -= 1

	if p1.timeout_temperature_water_in < 1:
		message = "Old data - temperature_water_in " + str(p1.timeout_temperature_water_in)
		write_log(message)
		write_history(message)
		old_data= 1

	if p1.timeout_temperature_water_out < 1:
		message = "Old data - temperature_water_out " + str(p1.timeout_temperature_water_out)
		write_log(message)
		write_history(message)
		old_data= 1
	if p1.timeout_temperature_smoke < 1:
		message = "Old data - temperature_smoke " + str(p1.timeout_temperature_smoke)
		write_log(message)
		write_history(message)
		old_data= 1


	if p1.r_mode == p1.MODE_OFFLINE:
		if all_data_is_available == 1 and old_data == 0:
			p1.r_mode = p1.MODE_ONLINE
			write_log("MODE_OFFLINE -> MODE_ONLINE")
			p1.r_inertia = p1.g_inertia
			message = 'MODE_ONLINE'
			gow_publishLog(p1, message )
	if p1.r_mode == p1.MODE_ONLINE:
		if old_data == 1:
			p1.r_mode = p1.MODE_OFFLINE
			write_log("MODE_ONLINE -> MODE_OFFLINE")
			message = 'MODE_OFFLINE'
			gow_publishLog(p1, message )
		if p1.r_state == p1.STATE_OFF:
			p1.r_onoff -= 1
			if p1.r_onoff < 0:
				p1.r_onoff = 0
			if p1.temperature_smoke > p1.g_minsmoke:
				p1.r_state = p1.STATE_WARMING
				write_log("STATE_OFF -> STATE_WARMING")
				message = 'STATE_WARMING'
				gow_publishLog(p1, message )
		if p1.r_state == p1.STATE_WARMING:
			p1.r_onoff += 1
			if p1.r_onoff == p1.g_onoff:
				p1.r_state = p1.STATE_ON
				p1.r_inertia = 0
				write_log("STATE_WARMING -> STATE_ON")
				message = 'STATE_ON'
				gow_publishLog(p1, message )
			if p1.temperature_smoke < p1.g_minsmoke:
				p1.r_state = p1.STATE_OFF
				write_log("STATE_WARMING -> STATE_OFF")
				p1.r_onoff = 0
				message = 'STATE_OFF'
				gow_publishLog(p1, message )
		if p1.r_state == p1.STATE_ON:
			action = 0
			if p1.r_inertia > 0: # delay after latest order
				p1.r_inertia -= 1
				action += 1
			if p1.temperature_smoke < p1.g_minsmoke: # heater is off
				action += 2
				p1.r_state = p1.STATE_OFF
				write_log("STATE_ON -> STATE_OFF")
				p1.r_onoff = 0
				message = 'STATE_OFF'
				gow_publishLog(p1, message )
			if p1.temperature_indoor > 20: # no warming above 20
				action += 4
			
			y = 

    			url = 'http://' + co.c_ds_uri[0] + '/' + co.c_ds_topic[0] + '/payload.json'
    			print url
    			tw.r_target = lib_readJsonPayload(url,'target')
    			print tw.r_target

			tmp1 = y*p1.g_relax
			tmp2 = p1.temperature_water_out*p1.g_relax
			tmp3 = tmp1 - tmp2
			steps = round(tmp3)
			publishFrequence(p1.temperature_water_out)
			print "tmp1=" + str(tmp1) + " tmp2="+str(tmp2) + " tmp3=" + str(tmp3)
			print "g_relax = " + str(p1.g_relax)
			print "steps = " + str(steps)
			print "temperature_water_out = " + str(p1.temperature_water_out)
			publishStep(steps)
			if p1.temperature_water_in > p1.temperature_water_out and steps < 0: # no cooling
				action += 8
			if abs(steps) < p1.g_minsteps: # min steps
				action += 16

			energy = p1.temperature_water_out - p1.temperature_water_in
			if energy > p1.g_maxenergy and steps > 0:
				action += 64

			if steps > 0:
				direction = COUNTERCLOCKWISE
			if steps < 0:
				direction = CLOCKWISE
			if steps == 0:
				action += 32

			if steps > p1.g_maxsteps:
				steps = p1.g_maxsteps
			if steps < -p1.g_maxsteps:
				steps = -p1.g_maxsteps
				
			#show_action_bit_info(action)
			
			if action == 0 and p1.r_stop == 0:
				steps = abs(steps)
				publishStepperMsg(int(steps), direction)
				print ">>>>>> Move Stepper " + str(steps) + " " + str(direction)
				p1.r_inertia = p1.g_inertia
				message = 'Auto steps: ' + str(steps) + ' dir: ' + str(direction)
				gow_publishLog(p1, message )
#========================================================================
	show_state_mode(p1)
   	if energy < 999:
		publishEnergyMsg(energy)
		
	# Current Configuration
	payload  = '{\n'
	payload += '"mintemp" : "' + str(p1.g_mintemp) + '",\n'
	payload += '"maxtemp" : "' + str(p1.g_maxtemp) + '",\n'
	payload += '"minheat" : "' + str(p1.g_minheat) + '",\n'
	payload += '"maxheat" : "' + str(p1.g_maxheat) + '",\n'
	payload += '"x_0" : "' + str(p1.g_x_0) + '",\n'
	payload += '"y_0" : "' + str(p1.g_y_0) + '",\n'
	payload += '"relax" : "' + str(p1.g_relax) + '",\n'
	payload += '"minsmoke" : "' + str(p1.g_minsmoke) + '",\n'
	payload += '"minsteps" : "' + str(p1.g_minsteps) + '",\n'
	payload += '"maxsteps" : "' + str(p1.g_maxsteps) + '",\n'
	payload += '"maxenergy" : "' + str(p1.g_maxenergy) + '",\n'
	
	payload += '"flags" : "' + str(action) + '",\n'
	payload += '"steps" : "' + str(steps) + '",\n'
	payload += '"target" : "' + str(y) + '",\n'
	payload += '"mode" : "' + str(p1.r_mode) + '",\n'
	payload += '"state" : "' + str(p1.r_state) + '",\n'
	payload += '"inertia" : "' + str(p1.g_inertia) + '",\n'
	payload += '"cur_inertia" : "' + str(p1.r_inertia) + '",\n'
	payload += '"onoff" : "' + str(p1.r_onoff) + '",\n'
	payload += '"errors" : "' + str(p1.r_errors) + '",\n'
	payload += '"stop" : "' + str(p1.r_stop) + '",\n'
	payload += '"bias" : "' + str(p1.r_bias) + '",\n'
	payload += '"temperature_outdoor" : "' + str(p1.temperature_outdoor) + '",\n'
	payload += '"temperature_water_out" : "' + str(p1.temperature_water_out) + '",\n'
	payload += '"temperature_water_in" : "' + str(p1.temperature_water_in) + '",\n'
	payload += '"temperature_smoke" : "' + str(p1.temperature_smoke) + '"\n'
	payload += '}\n'
	msg = publishGowDynamic(p1,payload)
	# STEPPER,<direcion>,<steps>
	if ":" in msg:
		p = msg.split(':')
		#print p[1]
		q = p[1].split(",")
		m = len(q)
		if m == 1:
			if q[0] == 'stopcontrol':
				message = 'Stop control: '
				gow_publishLog(p1, message )
				p1.r_stop = 1
			if q[0] == 'startcontrol':
				message = 'Start control: '
				gow_publishLog(p1, message )
				p1.r_stop = 0
		if m == 2:
			if q[0] == 'bias':
				p1.r_bias = float(q[1])
				message = 'Bias: ' + str(p1.r_bias)
				gow_publishLog(p1, message )	

			if q[0] == 'inertia':
				p1.g_inertia = float(q[1])
				message = 'Inertia: ' + str(p1.r_bias)
				gow_publishLog(p1, message )	

			if q[0] == 'onoff':
				p1.g_onoff = float(q[1])
				message = 'onoff: ' + str(p1.g_onoff)
				gow_publishLog(p1, message )

			if q[0] == 'minsmoke':
				p1.g_minsmoke = float(q[1])
				message = 'minsmoke: ' + str(p1.g_minsmoke)
				gow_publishLog(p1, message )	

			if q[0] == 'minsteps':
				p1.g_minsteps = float(q[1])
				message = 'minsteps: ' + str(p1.g_minsteps)
				gow_publishLog(p1, message )	

			if q[0] == 'maxsteps':
				p1.g_maxsteps = float(q[1])
				message = 'maxsteps: ' + str(p1.g_maxsteps)
				gow_publishLog(p1, message )	

			if q[0] == 'maxenergy':
				p1.g_maxenergy = float(q[1])
				message = 'maxenergy: ' + str(p1.g_maxenergy)
				gow_publishLog(p1, message )	
				
		if m == 3:
			direction = CLOCKWISE
			steps = int(q[2])
			steps = abs(steps)
			ok = 0
			if q[0] == 'stepper':
				ok += 1
			if q[1] == 'cw':
				#print 'cw'
				direction = CLOCKWISE
				ok += 1
			if q[1] == 'ccw':
				#print 'ccw'
				direction = COUNTERCLOCKWISE
				ok += 1
			if steps > 5 and steps < 100:
				ok += 1
			if ok == 3:
				publishStepperMsg(steps,direction)
				message = 'Manual steps: ' + str(steps) + ' dir: ' + str(direction)
				gow_publishLog(p1, message )
	return
#=====================================================
def getTopicHash(topic):
    res = topic['top'] + topic['global'] + topic['local'] + topic['client_id'] + str(topic['message_type']) + str(topic['stream_index'])
    tres = hash(res)
    tres = tres% 10**8
    return tres

#=====================================================
def subscribe_to_topic(par,msgt):
    configuration = ioant.get_configuration()
    topic = ioant.get_topic_structure()
    topic['top'] = 'live'
    topic['global'] = configuration["subscribe_topic"][par]["global"]
    topic['local'] = configuration["subscribe_topic"][par]["local"]
    topic['client_id'] = configuration["subscribe_topic"][par]["client_id"]
    topic['message_type'] = ioant.get_message_type(msgt)
    topic['stream_index'] = configuration["subscribe_topic"][par]["stream_index"]
    print "Subscribe to: " + str(topic)
    ioant.subscribe(topic)
    shash = getTopicHash(topic)
    return shash
#=====================================================
def find_extreme(p1):
	t = datetime.datetime.now() 
	print "min-max: " + str(p1.v1) + " " + str(p1.v2) + " " + str(p1.v3)
	if p1.v1 > p1.v2 and p1.v2 > p1.v3:
		print "values falling"
	if p1.v1 < p1.v2 and p1.v2 < p1.v3:
		print "values rising"
	if p1.v1 >= p1.v2 and p1.v2 < p1.v3: # minimum
		d = t - p1.tmin
		f = d.seconds
		p1.tmin = t
		#publishFrequence(f)
		publishExtreme(1)
	if p1.v1 <= p1.v2 and p1.v2 > p1.v3: # maximum
		d = t - p1.tmax
		f = d.seconds
		p1.tmax = t
		#publishFrequence(f)
		publishExtreme(2)	
#=====================================================
def setup(configuration):
	global s1

	s1.v1 = 30.0
	s1.v2 = 30.0
	s1.v3 = 30.0

	s1.tmin = datetime.datetime.now() 
	s1.tmax = datetime.datetime.now() 

	s1.r_counter = 0
	s1.r_errors = 0
	s1.r_bias = 0.0
	
	s1.STATE_INIT = 0
	s1.STATE_OFF = 1
	s1.STATE_WARMING = 2
	s1.STATE_ON = 3
	s1.MODE_OFFLINE = 1
	s1.MODE_ONLINE = 2
	
	s1.g_minsteps = 5
	s1.g_maxsteps = 30
	s1.g_minsmoke = 27
	s1.g_mintemp = -7
	s1.g_maxtemp = 10
	s1.g_minheat = 20
	s1.g_maxheat = 40
	
	s1.g_x_0 = 0
	s1.g_y_0 = 35
	s1.g_relax = 3.0
	s1.g_maxenergy = 4.0
	s1.g_period = 5000
	s1.g_gow_server = 'gow.test.com'
	s1.g_gow_topic = 'etc/etc/etc/0'

	s1.temperature_indoor    = 999
	s1.temperature_outdoor   = 999
	s1.temperature_water_in  = 999
	s1.temperature_water_out = 999
	s1.temperature_smoke     = 999

	s1.timeout_temperature_indoor = 60
	s1.timeout_temperature_outdoor = 60
	s1.timeout_temperature_water_in = 60
	s1.timeout_temperature_water_out = 60
	s1.timeout_temperature_smoke = 60
	
	ioant.setup(configuration)
	configuration = ioant.get_configuration()
	tempv   = int(configuration["ioant"]["communication_delay"])
	s1.g_period   = round(tempv/1000)
	s1.g_gow_server = str(configuration["gow_server"])
	s1.g_gow_topic = str(configuration["gow_topic"])
	s1.g_minsteps = int(configuration["algorithm"]["minsteps"])
	s1.g_maxsteps = int(configuration["algorithm"]["maxsteps"])
	s1.g_minsmoke = float(configuration["algorithm"]["minsmoke"])
	s1.g_mintemp = float(configuration["algorithm"]["mintemp"])
	s1.g_maxtemp = float(configuration["algorithm"]["maxtemp"])
	s1.g_minheat = float(configuration["algorithm"]["minheat"])
	s1.g_maxheat = float(configuration["algorithm"]["maxheat"])
	s1.g_x_0 = float(configuration["algorithm"]["x_0"])
	s1.g_y_0 = float(configuration["algorithm"]["y_0"])
	s1.g_onoff = int(configuration["algorithm"]["onofftime"])
	s1.g_inertia = int(configuration["algorithm"]["inertia"])
	s1.g_relax = float(configuration["algorithm"]["relax"])
	s1.g_maxenergy = float(configuration["algorithm"]["maxenergy"])

	s1.r_state = s1.STATE_OFF
	write_log("START -> STATE_OFF")
	s1.r_mode = s1.MODE_OFFLINE
	write_log("START -> MODE_OFFLINE")
	s1.r_inertia = s1.g_inertia
	s1.r_onoff = s1.g_onoff

	init_log()
	init_history()
	publishGowStatic(s1)
#=====================================================
def loop():
    global s1
    ioant.update_loop()
    s1.r_counter += 1
    if s1.r_counter > 999999:
	s1.r_counter = 0
    
    mtemp = s1.r_counter % 5
    if mtemp == 0:
	heater_model(s1)

#=====================================================
def on_message(topic, message):
	global s1
	""" Message function. Handles recieved message from broker """
	if topic["message_type"] == ioant.get_message_type("Temperature"):
		shash = getTopicHash(topic)
		#logging.info("Temp = "+str(message.value)+" hash="+str(shash))
		if shash == s1.hash_indoor:
			print "===> indoor " + str(message.value)
			s1.temperature_indoor_prev = s1.temperature_indoor
			s1.temperature_indoor = message.value
			diff  = s1.temperature_indoor - s1.temperature_indoor_prev
			if abs(diff) > 10 and s1.temperature_indoor_prev != 999:
				message = 'Temperature indoor error: cur=' + str(s1.temperature_indoor) + ' prev=' + str(s1.temperature_indoor_prev)
				gow_publishLog(s1, message )
				s1.temperature_indoor = s1.temperature_indoor_prev
				s1.r_errors += 1
			s1.timeout_temperature_indoor = 60
		if shash == s1.hash_outdoor:
			print "===> outdoor " + str(message.value)
			s1.temperature_outdoor_prev = s1.temperature_outdoor
			s1.temperature_outdoor = message.value
			diff  = s1.temperature_outdoor - s1.temperature_outdoor_prev
			if abs(diff) > 10 and s1.temperature_outdoor_prev != 999:
				message = 'Temperature outdoor error: cur=' + str(s1.temperature_outdoor) + ' prev=' + str(s1.temperature_outdoor_prev)
				gow_publishLog(s1, message )
				s1.temperature_outdoor = s1.temperature_outdoor_prev
				s1.r_errors += 1
			s1.timeout_temperature_outdoor = 60
		if shash == s1.hash_water_in:
			print "===> water in " + str(message.value)
			s1.temperature_water_in_prev = s1.temperature_water_in
			s1.temperature_water_in = message.value
			diff  = s1.temperature_water_in - s1.temperature_water_in_prev
			if abs(diff) > 10 and s1.temperature_water_in_prev != 999:
				message = 'Temperature water in error: cur=' + str(s1.temperature_water_in) + ' prev=' + str(s1.temperature_water_in_prev)
				gow_publishLog(s1, message )
				s1.temperature_water_in = s1.temperature_water_in_prev
				s1.r_errors += 1
			s1.timeout_temperature_water_in = 60
		if shash == s1.hash_water_out:
			print "===> water out " + str(message.value)
			s1.temperature_water_out_prev = s1.temperature_water_out
			s1.temperature_water_out = message.value
			diff  = s1.temperature_water_out - s1.temperature_water_out_prev
			if abs(diff) > 10 and s1.temperature_water_out_prev != 999:
				message = 'Temperature water out error: cur=' + str(s1.temperature_water_out) + ' prev=' + str(s1.temperature_water_out_prev)
				gow_publishLog(s1, message )
				s1.temperature_water_out = s1.temperature_water_out_prev
				s1.r_errors += 1
			s1.timeout_temperature_water_out = 60
		if shash == s1.hash_smoke:
			print "===> smoke " + str(message.value)
			s1.temperature_smoke_prev = s1.temperature_smoke
			s1.temperature_smoke = message.value
			diff  = s1.temperature_smoke - s1.temperature_smoke_prev
			if abs(diff) > 10 and s1.temperature_smoke_prev != 999:
				message = 'Temperature smoke error: cur=' + str(s1.temperature_smoke) + ' prev=' + str(s1.temperature_smoke_prev)
				gow_publishLog(s1, message )
				s1.temperature_smoke = s1.temperature_smoke_prev
				s1.r_errors += 1
			s1.timeout_temperature_smoke = 60
			s1.v1 = s1.v2
			s1.v2 = s1.v3
			s1.v3 = s1.temperature_smoke
			find_extreme(s1)

    #if "Temperature" == ioant.get_message_type_name(topic[message_type]):

#=====================================================
def on_connect():
    """ On connect function. Called when connected to broker """
    global s1

    # There is now a connection
    s1.hash_indoor    = subscribe_to_topic("indoor","Temperature")
    s1.hash_outdoor   = subscribe_to_topic("outdoor","Temperature")
    s1.hash_water_in  = subscribe_to_topic("water_in","Temperature")
    s1.hash_water_out = subscribe_to_topic("water_out","Temperature")
    s1.hash_smoke     = subscribe_to_topic("smoke","Temperature")

# =============================================================================
# Above this line are mandatory functions
# =============================================================================
# Mandatory line
ioant = IOAnt(on_connect, on_message)
