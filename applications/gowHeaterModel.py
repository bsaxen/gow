# =============================================
# File: gowHeaterModel.py
# Author: Benny Saxen
# Date: 2019-01-25
# Description: heater control algorithm
# Next Generation
# 90 degrees <=> 1152/4 steps = 288
# =============================================
# Input Datastreams
# 	smoke temperature
#	water out temperature
#	water in temperature
#
# Output datastreams
#	mode
#	state
#	inertia
#	cooling_warming
#
# Actuator
#	set target temperature of water out
#	turn model on or off
# =============================================

import hashlib
import math
import urllib
import urllib2
import time
import datetime
from lib import *

STATE_INIT    = 0
STATE_OFF     = 1
STATE_WARMING = 2
STATE_ON      = 3

MODE_OFFLINE  = 1
MODE_ONLINE   = 2
#==============================================
class twin:
   r_now = ''
   # state
   r_state     = 0
   r_mode      = 0
   r_inertia   = 0
   r_coolwarm  = 0
   r_counter   = 0

   g_inertia   = 0
   g_coolwarm  = 0
   g_period    = 0

   # Subscriptions
   temperature_water_in  = 0.0
   temperature_water_out = 0.0
   temperature_smoke     = 0.0
   temp_smoke_ave        = 0.0
   v1 = 0.0
   v2 = 0.0
   v3 = 0.0

   # algorithm configuration
   g_minheat = 0.0
   g_maxheat = 0.0
   g_relax   = 4.0
   g_min_smoke = 0.0
   g_minsteps  = 0
   g_maxsteps  = 0
   g_defsteps  = 0
   g_max_energy = 0

   # other
   g_tmax = 0
   g_tmin = 0

#======================================

#=====================================================
def heater_model(tt1,cc1):
	print "Heater Model"
	print tt1.temperature_water_out
	print tt1.temperature_water_in
	print tt1.temperature_smoke

#=============================================
# setup
#=============================================
app = 'gowHeaterModel'
print "======== gowHeaterModel version 2019-01-25 =========="
t1 = twin()
t1.r_now = datetime.datetime.now()

c1 = configuration()

confile = "gowheatermodel.conf"
lib_readConfiguration(confile,c1)


d1 = datastream()
tmin = datetime.datetime.now()
tmax = datetime.datetime.now()

#=============================================
# loop
#=============================================
print "Loop"
counter = 0
while True:

	t1.r_mode = MODE_ONLINE
	url = lib_buildUrl(c1.c_ds_uri[0],c1.c_ds_topic[0])
	t1.temperature_water_out = lib_readJsonPayload(url,c1.c_ds_param[0])
	if t1.temperature_water_out == 123456789:
		t1.r_mode = MODE_OFFLINE
		msg = "MODE_OFFLINE -old data: temperature water out "
		log(app,msg)

	url = lib_buildUrl(c1.c_ds_uri[1],c1.c_ds_topic[1])
	t1.temperature_water_in = lib_readJsonPayload(url,c1.c_ds_param[1])
	if t1.temperature_water_in == 123456789:
		t1.r_mode = MODE_OFFLINE
		msg = "MODE_OFFLINE -old data: temperature water in "
		log(app,msg)

	url = lib_buildUrl(c1.c_ds_uri[2],c1.c_ds_topic[2])
	t1.temperature_smoke = lib_readJsonPayload(url,c1.c_ds_param[2])
	if t1.temperature_smoke == 123456789:
		t1.r_mode = MODE_OFFLINE
		msg = "MODE_OFFLINE -old data: temperature smoke "
		log(app,msg)

	url = lib_buildUrl(c1.c_ds_uri[2],c1.c_ds_topic[2])
	t1.temperature_smoke = lib_readJsonPayload(url,c1.c_ds_param[2])
	if t1.temperature_smoke == 123456789:
		t1.r_mode = MODE_OFFLINE
		msg = "MODE_OFFLINE -old data: temperature smoke "
		log(app,msg)

	if t1.r_mode == MODE_ONLINE:
		heater_model(t1,c1)

	counter += 1
	payload = '{"mode":"' + str(t1.r_mode) + '"}'
	lib_publish(c1, c1.c_topic1,payload,counter )

	payload = '{"state":"' + str(t1.r_state) + '"}'
	lib_publish(c1, c1.c_topic2,payload,counter )

	payload = '{"inertia":"' + str(t1.r_inertia) + '"}'
	lib_publish(c1, c1.c_topic3,payload,counter )

	payload = '{"coolwarm":"' + str(t1.r_coolwarm) + '"}'
	lib_publish(c1, c1.c_topic4,payload,counter )

	print "---> sleep " + str(c1.c_period)
	time.sleep(float(c1.c_period))

#===================================================
# End of file
#===================================================
