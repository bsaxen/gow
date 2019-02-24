#!/usr/bin/python
#=============================================
# File.......: gowDeviceStatus.py
# Date.......: 2019-02-23
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
status   = []
#=============================================
# setup
#=============================================

confile = 'gowdevicestatus.conf'
lib_readConfiguration(confile,co)
lib_gowPublishStatic(co)

domain = co.mydomain

lib_gowPublishDynamic(co,md,'{}')

device_list = lib_listDomainDevices(domain)
no_devices = len(device_list) - 1
print "Number of devices: " + str(no_devices)

max_period = 0
for num in range(0,no_devices):
    url_static  = lib_buildUrl(domain,device_list[num],'static')
    url_dynamic = lib_buildUrl(domain,device_list[num],'dynamic')
    url_payload = lib_buildUrl(domain,device_list[num],'payload')


    period = float(lib_readJsonMeta(url_static,'period'))
    period = int(period)
    print num
    print period
    desc = lib_readJsonMeta(url_static,'desc')
    print desc
    schedule.append(period)
    work.append(period)
    no = float(lib_readJsonMeta(url_dynamic,'no'))
    running.append(no)
    status.append(0)
    print no
#=============================================
# loop
#=============================================
now = datetime.datetime.now()#.strftime("%Y-%m-%d %H:%M:%S")
time.sleep(3)
total_duration = 0
md.mycounter = 0
report_freq = int(co.myperiod)

while True:
    then = now
    now = datetime.datetime.now()#.strftime("%Y-%m-%d %H:%M:%S")
    duration = now - then
    duration_in_s = duration.total_seconds()
    total_duration += duration_in_s
    time.sleep(1)
    md.mycounter += 1
    if md.mycounter%report_freq == 0:
        payload = '{'
        for num in range(0,no_devices-2):
            payload += '\"' + device_list[num] + '\" :' + '\"' + str(status[num]) +'\",'
        payload += '\"' + device_list[no_devices-1] + '\" :' + '\"' + str(status[no_devices-1]) +'\"'
        payload += '}'
        print payload
        lib_gowPublishDynamic(co,md,payload)
    for num in range(0,no_devices):
        work[num] -= 1
        print str(num) + " www " + str(work[num])
        if work[num] == 0:
            url_static  = lib_buildUrl(domain,device_list[num],'static')
            url_dynamic = lib_buildUrl(domain,device_list[num],'dynamic')
            url_payload = lib_buildUrl(domain,device_list[num],'payload')
            work[num] = schedule[num]

            period = float(lib_readJsonMeta(url_static,'period'))
            print period
            period = int(period)
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
                ok = 0
            if delta_no > 1:
                print "Missing data: " + str(delta_no)
                ok = 1
            if delta_no == 0:
                print "No update of data: " + str(delta_no)
                ok = 2
            if delta_no < 0:
                print "Wrap around of data: " + str(delta_no)
                ok = 3

            running[num] = no
            status[num] = ok

#===================================================
# End of file
#===================================================
