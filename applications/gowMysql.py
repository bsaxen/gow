#!/usr/bin/python
#=============================================
# File.......: gowMysql.py
# Date.......: 2019-01-05
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
#=============================================
# Configuration
#=============================================
cUrl        = 'gow.simuino.com'
cNtop	    = 1
cTopic1     = 'kvv32/test/temperature/0'
cDbHost     = '192.168.1.85'
cDbName     = 'gow'
cDbUser     = 'folke'
cDbPassword = 'something'
cDbTableName1 = 'something'
cDbTableName2 = 'something'
#===================================================
def readConfiguration():
	try:
		fh = open('configuration.txt', 'r') 
		for line in fh: 
			print line
			word = line.split()
			if word[0] == 'url':
				cUrl    = word[1]
			if word[0] == 'url':
				cNtop   = word[1]
			if word[0] == 'topic1':
				cTopic1    = word[1]
			if word[0] == 'topic2':
				cTopic2    = word[1]
			if word[0] == 'host':
				cDbHost      = word[1]
			if word[0] == 'database':
				cDbName = word[1]
			if word[0] == 'user':
				cDbUser      = word[1]
			if word[0] == 'pswd':
				cDbPassword  = word[1]

			if word[0] == 'tablename1':
				cDbTableName1  = word[1]
				print cDbTableName1 + "benny"
			if word[0] == 'tablename2':
				cDbTableName2  = word[1]
		fh.close()
	except:
		fh = open('configuration.txt', 'w')
		fh.write('url        gow.simuino.com\n')
		fh.write('ntop       1\n')
		fh.write('topic1     kvv32/test/temperature/0\n')
		fh.write('topic2     kvv32/test/temperature/1\n')
		fh.write('host       192.168.1.85\n')
		fh.write('database   gow\n')
		fh.write('user       folke\n')
		fh.write('pswd       hm\n')
		fh.write('tablename1 hm\n')
		fh.write('tablename2 hm\n')
		fh.close()
	return
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
    db = MySQLdb.connect(host=cDbHost,
                     user=cDbUser,
                     #passwd=cDbPassword,
                     db=cDbName)
    cursor = db.cursor()
    sql = "INSERT INTO "+ xTable + " (`id`, " + xPar + ", `ts`) VALUES (NULL," + str(xValue) + ", CURRENT_TIMESTAMP)"
    cursor.execute(sql)
    db.commit()
    db.close()
    
#=============================================
# setup
#=============================================    
readConfiguration()
#=============================================
# loop
#=============================================
while True:

	print "t1 " + cDbTableName1
	if cNtop > 0:
		url = 'http://' + cUrl + '/' + cTopic1 + '/device.json'
   		period = float(gowReadJsonMeta(url,'period'))
    		x      = float(gowReadJsonPayload(url,'temp1'))
    		gowMysqlInsert(cDbTableName1,'value',x)

	if cNtop > 1:
   		url = 'http://' + cUrl + '/' + cTopic2 + '/device.json'
   		period = float(gowReadJsonMeta(url,'period'))
    		x      = float(gowReadJsonPayload(url,'temp2'))
    		gowMysqlInsert(cDbTableName2,'value',x)
		
    	print "sleep " + str(period)
    	time.sleep(period)
