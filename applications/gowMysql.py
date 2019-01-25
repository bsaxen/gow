#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2019-01-25
# Author.....: Benny Saxen
# Description:
#=============================================
# Libraries
#=============================================
import MySQLdb
import requests
import json
import urllib2
import time
import datetime
import sys
from lib import *
#=============================================
# Configuration
#=============================================
schedule = []
work     = []
running  = []
#=============================================
# setup
#=============================================
r1 = configuration()
confile = 'gowmysql.conf'
lib_readConfiguration(confile,r1)
print "Number of datastreams: " + str(r1.c_nds)

max_period = 0
for num in range(0,r1.c_nds):
    url = 'http://' + r1.c_ds_uri[num] + '/' + r1.c_ds_topic[num] + '/device.json'
    print url
    period = float(lib_readJsonMeta(url,'period'))
    print period
    #if period > max_period:
    #    max_period = period
    schedule.append(period)
    work.append(period)
    no = float(lib_readJsonMeta(url,'no'))
    running.append(no)
    print no
    x      = float(lib_readJsonPayload(url,r1.c_ds_param[num]))
    print x
    lib_mysqlInsert(r1,1,r1.c_ds_table[num],'value',x)
#=============================================
# loop
#=============================================
now = datetime.datetime.now()#.strftime("%Y-%m-%d %H:%M:%S")
time.sleep(3)
total_duration = 0
while True:
    then = now
    now = datetime.datetime.now()#.strftime("%Y-%m-%d %H:%M:%S")
    #print now
    duration = now - then                         # For build-in functions
    duration_in_s = duration.total_seconds()
    #print duration_in_s
    total_duration += duration_in_s
    #print total_duration
    #print "sleep " + str(1)
    time.sleep(1)

    for num in range(0,nds):
        work[num] -= 1
        #print str(num) + " " + str(work[num])
        if work[num] == 0:
            work[num] = schedule[num]
            url = 'http://' + c_ds_url[num] + '/' + c_ds_topic[num] + '/device.json'
            print url
            period = float(lib_readJsonMeta(url,'period'))
            print period
            #if period > max_period:
            #    max_period = period
            schedule[num] = period
            no = float(lib_readJsonMeta(url,'no'))
            print no
            delta_no = no - running[num]
            ok = 0
            if delta_no == 1:
                print "Correct data: " + str(delta_no)
                ok = 1
            if delta_no > 1:
                print "Missing data: " + str(delta_no)
                ok = 1
            if delta_no == 0:
                print "No update of data: " + str(delta_no)
            if delta_no < 0:
                print "Wrap around of data: " + str(delta_no)
                ok = 1
            if ok == 1:
                running[num] = no
                x  = float(lib_readJsonPayload(url,r1.c_ds_param[num]))
                print x
                lib_mysqlInsert(r1,0,r1.c_ds_table[num],'value',x)
