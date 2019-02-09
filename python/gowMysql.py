#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2019-02-09
# Author.....: Benny Saxen
# Description:
#=============================================
# Libraries
#=============================================
from gowLib import *
#=============================================
# Configuration
#=============================================
schedule = []
work     = []
running  = []
#=============================================
# setup
#=============================================
print "======== gowMysql version 2019-02-01 =========="
r1 = configuration()
d1 = datastream()
confile = 'gowmysql.conf'
lib_readConfiguration(confile,r1)
print "Number of datastreams: " + str(r1.c_nds)

max_period = 0
for num in range(0,r1.c_nds):
    url_static  = lib_buildUrl(r1.c_ds_uri[num],r1.c_ds_topic[num],'static')
    url_dynamic = lib_buildUrl(r1.c_ds_uri[num],r1.c_ds_topic[num],'dynamic')
    url_payload = lib_buildUrl(r1.c_ds_uri[num],r1.c_ds_topic[num],'payload')

    period = float(lib_readJsonMeta(url_static,'period'))
    print period
    desc = lib_readJsonMeta(url_static,'desc')
    print desc
    #if period > max_period:
    #    max_period = period
    schedule.append(period)
    work.append(period)
    no = float(lib_readJsonMeta(url_dynamic,'no'))
    running.append(no)
    print no
    x      = float(lib_readJsonPayload(url_payload,r1.c_ds_param[num]))
    print x
    if r1.c_ds_table[num] == 'auto':
        table = desc
    else:
        table = r1.c_ds_table[num]
        
    lib_mysqlInsert(r1,1,table,'value',x)
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

    for num in range(0,r1.c_nds):
        url_static  = lib_buildUrl(r1.c_ds_uri[num],r1.c_ds_topic[num],'static')
        url_dynamic = lib_buildUrl(r1.c_ds_uri[num],r1.c_ds_topic[num],'dynamic')
        url_payload = lib_buildUrl(r1.c_ds_uri[num],r1.c_ds_topic[num],'payload')
        work[num] -= 1
        #print str(num) + " " + str(work[num])
        if work[num] == 0:
            work[num] = schedule[num]
            
            period = float(lib_readJsonMeta(url_static,'period'))
            print period
            desc = lib_readJsonMeta(url_static,'desc')
            print desc
            schedule[num] = period

            no = float(lib_readJsonMeta(url_dynamic,'no'))
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
                x  = float(lib_readJsonPayload(url_payload,r1.c_ds_param[num]))
                print x
                if r1.c_ds_table[num] == 'auto':
                    table = desc
                else:
                    table = r1.c_ds_table[num]
                    
                lib_mysqlInsert(r1,0,table,'value',x)
#===================================================
# End of file
#===================================================
