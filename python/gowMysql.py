#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2019-03-07
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
    print num
    domain = co.ds_domain[num]
    device = co.ds_device[num]
    param  = co.ds_db_par[num]
    period = float(lib_readStaticParam(co,ds,domain,device,'period'))
    print period
    desc = lib_readStaticParam(co,ds,domain,device,'desc')
    print desc
    schedule.append(period)
    work.append(period)
    counter = float(lib_readDynamicParam(co,ds,domain,device,'counter'))
    running.append(counter)
    print counter
    x      = float(lib_readPayloadParam(co,ds,domain,device,param))
    print x
    if co.ds_db_table[num] == 'auto':
        table = desc
    else:
        table = co.ds_db_table[num]
        
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

    for num in range(0,co.nds):
        domain = co.ds_domain[num]
        device = co.ds_device[num]
        param  = co.ds_db_par[num]
        work[num] -= 1
        #print str(num) + " " + str(work[num])
        if work[num] == 0:
            work[num] = schedule[num]
            
            period = float(lib_readStaticParam(co,ds,domain,device,'period'))
            print period
            desc = lib_readStaticParam(co,ds,domain,device,'desc')
            print desc
            schedule[num] = period

            counter = float(lib_readDynamicParam(co,ds,domain,device,'counter'))
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
                x  = float(lib_readPayloadParam(co,ds,domain,device,param))
                print x
                if co.ds_db_table[num] == 'auto':
                    table = desc
                else:
                    table = co.ds_db_table[num]
                    
                lib_mysqlInsert(co,0,table,'value',x)
#===================================================
# End of file
#===================================================
