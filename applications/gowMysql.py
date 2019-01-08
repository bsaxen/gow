#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2019-01-08
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

#if len (sys.argv) != 2 :
#    print "Usage: python gowMysql.py your-configuration.txt"
#    sys.exit (1)

#config_file = sys.argv[1]
config_file = 'configuration.txt'
print "Configuration used: " + config_file

#=============================================
# Configuration
#=============================================
cDbHost     = '192.168.1.85'
cDbName     = 'gow'
cDbUser     = 'folke'
cDbPassword = 'something'
c_url    = []
c_topic  = []
c_table  = []
c_param  = []
schedule = []
work     = []
running  = []
#===================================================
def readConfiguration():
    global cDbHost
    global cDbName
    global cDbUser
    global cDbPassword
    global c_url
    global c_topic
    global c_table
    global c_param

    n = 0
    try:
        print config_file
        fh = open(config_file, 'r')
        for line in fh:
            #print line
            word = line.split()
            if word[0] == 'host':
                cDbHost = word[1]
                print cDbHost
            if word[0] == 'database':
                cDbName = word[1]
                print cDbName
            if word[0] == 'user':
                cDbUser = word[1]
                print cDbUser
            if word[0] == 'pswd':
                cDbPassword  = word[1]
                print cDbPassword
            if word[0] == 'stream':
                c_url.append(word[1])
                c_topic.append(word[2])
                c_table.append(word[3])
                c_param.append(word[4])
                print str(n) + " " + c_topic[n]
                n += 1
        fh.close()
    except IOError:
        print "Configuration file not found " + config_file + " - configuration.txt template created"
        fh = open('configuration.txt', 'w')
        fh.write('host       192.168.1.85\n')
        fh.write('database   gow\n')
        fh.write('user       folke\n')
        fh.write('pswd       hm\n')
        fh.write('stream     url topic table parameter\n')
        fh.close()
        sys.exit (1)
    return(n)
#=============================================
def gowReadJsonMeta(url,par):
#=============================================
    r = urllib2.urlopen(url)
    j = json.load(r)
    x =  j['gow'][par]
    return x
#=============================================
def gowReadJsonPayload(url,par):
#=============================================
    r = urllib2.urlopen(url)
    j = json.load(r)
    x =  j['gow']['payload'][par]
    return x
#=============================================
def gowMysqlInsert(xTable,xPar,xValue):
#=============================================
    global cDbHost
    global cDbName
    global cDbUser
    global cDbPassword
    print xTable + xPar + str(xValue)
    db = MySQLdb.connect(host=cDbHost,user=cDbUser,db=cDbName)
    cursor = db.cursor()
    sql = "CREATE TABLE IF NOT EXISTS " + xTable + " (id int(11) NOT NULL AUTO_INCREMENT,value float,ts timestamp, PRIMARY KEY (id))"
    cursor.execute(sql)
    sql = "INSERT INTO "+ xTable + " (`id`, " + xPar + ", `ts`) VALUES (NULL," + str(xValue) + ", CURRENT_TIMESTAMP)"
    cursor.execute(sql)
    db.commit()
    db.close()

#=============================================
# setup
#=============================================
nds = readConfiguration()
print "Number of datastreams: " + str(nds)
max_period = 0
for num in range(0,nds):
    url = 'http://' + c_url[num] + '/' + c_topic[num] + '/device.json'
    print url
    period = float(gowReadJsonMeta(url,'period'))
    print period
    if period > max_period:
        max_period = period
    schedule.append(period)
    work.append(period)
    no = float(gowReadJsonMeta(url,'no'))
    running.append(no)
    print no
    x      = float(gowReadJsonPayload(url,c_param[num]))
    print x
    #gowMysqlInsert(cDbTableName,'value',x)
#=============================================
# loop
#=============================================
print "max period:" + str(max_period)
now = datetime.datetime.now()#.strftime("%Y-%m-%d %H:%M:%S")
time.sleep(3)
total_duration = 0
while True:
    then = now
    now = datetime.datetime.now()#.strftime("%Y-%m-%d %H:%M:%S")
    #print now
    duration = now - then                         # For build-in functions
    duration_in_s = duration.total_seconds()
    print duration_in_s
    total_duration += duration_in_s
    print total_duration
    print "sleep " + str(1)
    time.sleep(1)

    for num in range(0,nds):
        work[num] -= 1
        print str(num) + " " + str(work[num])
        if work[num] == 0:
            work[num] = schedule[num]
            url = 'http://' + c_url[num] + '/' + c_topic[num] + '/device.json'
            print url
            period = float(gowReadJsonMeta(url,'period'))
            print period
            if period > max_period:
                max_period = period
            schedule[num] = period
            no = float(gowReadJsonMeta(url,'no'))
            print no
            if no != running[num]:
                x  = float(gowReadJsonPayload(url,c_param[num]))
                print x
                #gowMysqlInsert(cDbTableName,'value',x)
