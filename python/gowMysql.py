#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2019-03-05
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
confile = 'gowmysql.conf'
lib_readConfiguration(confile,co)
print "Number of datastreams: " + str(co.nds)
lib_gowPublishMyStatic(co)

max_period = 0
for num in range(0,co.nds):
    url_static  = lib_buildAnyUrl(co.ds_uri[num],co.ds_topic[num],'static')
    url_dynamic = lib_buildAnyUrl(co.ds_uri[num],co.ds_topic[num],'dynamic')
    url_payload = lib_buildAnyUrl(co.ds_uri[num],co.ds_topic[num],'payload')

    period = float(lib_readJsonMeta(url_static,'period'))
    print period
    desc = lib_readJsonMeta(url_static,'desc')
    print desc
    schedule.append(period)
    work.append(period)
    counter = float(lib_readJsonMeta(url_dynamic,'counter'))
    running.append(counter)
    print counter
    x      = float(lib_readJsonPayload(url_payload,co.ds_param[num]))
    print x
    if co.ds_table[num] == 'auto':
        table = desc
    else:
        table = co.ds_table[num]
        
    lib_mysqlInsert(co,1,table,'value',x)
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
        url_static  = lib_buildAnyUrl(co.ds_uri[num],co.ds_topic[num],'static')
        url_dynamic = lib_buildAnyUrl(co.ds_uri[num],co.ds_topic[num],'dynamic')
        url_payload = lib_buildAnyUrl(co.ds_uri[num],co.ds_topic[num],'payload')
        work[num] -= 1
        #print str(num) + " " + str(work[num])
        if work[num] == 0:
            work[num] = schedule[num]
            
            period = float(lib_readJsonMeta(url_static,'period'))
            print period
            desc = lib_readJsonMeta(url_static,'desc')
            print desc
            schedule[num] = period

            counter = float(lib_readJsonMeta(url_dynamic,'counter'))
            print counter
            delta_counter = counter - running[num]
            ok = 0
            if delta_counter == 1:
                print "Correct data: " + str(delta_counter)
                ok = 1
            if delta_counter > 1:
                print "Missing data: " + str(delta_counter)
                ok = 1
            if delta_counter == 0:
                print "No update of data: " + str(delta_counter)
            if delta_counter < 0:
                print "Wrap around of data: " + str(delta_counter)
                ok = 1
            if ok == 1:
                running[num] = counter
                x  = float(lib_readJsonPayload(url_payload,co.ds_param[num]))
                print x
                if co.ds_table[num] == 'auto':
                    table = desc
                else:
                    table = co.ds_table[num]
                    
                lib_mysqlInsert(co,0,table,'value',x)
#===================================================
# End of file
#===================================================
