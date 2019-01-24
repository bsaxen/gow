# =============================================
# File: lib.py
# Author: Benny Saxen
# Date: 2019-01-24
# Description: GOW python library
# =============================================
import MySQLdb
import urllib
import urllib2
import time
import datetime

class configuration:
	c_url     = 'gow.simuino.com'
	c_server_app = 'gowServer.php'
	c_hw         = 'python'
	c_period     = 10
	c_wrap       = 999999
	c_topic1     = "topic1"
	c_topic2     = "topic2"
	c_topic3     = "topic3"
	c_action1    = "action1"
	c_action2    = "action2"
	c_action3    = "action3"

	# Heater algorithm
	c_mintemp = 0.0
   	c_maxtemp = 0.0
   	c_minheat = 0.0
   	c_maxheat = 0.0
   	c_x_0     = 0.0
   	c_y_0     = 0.0
   	c_relax   = 4.0
	c_minsmoke = 0.0
   	c_minsteps  = 0
   	c_maxsteps  = 0
   	c_defsteps  = 0  
   	c_maxenergy = 0 
	
	# database access
    	c_dbhost     = '192.168.1.85'
    	c_dbname     = 'gow'
    	c_dbuser     = 'myuser'
    	c_dbpassword = 'mypswd'	
#=====================================================
def lib_init_history(fname):
    try:
        f = open(fname,'w')
        f.write(fname)
        f.write('\n')
        f.close()
    except:
        print "ERROR init file " + fname
    return
#=====================================================
def lib_writeFile(fname,message,ts):
    try:
        f = open(fname,'a')
	if ts == 1:
		f.write(datetime.datetime.now().strftime("%y-%m-%d %H:%M:%S")+" ")
        f.write(message)
        f.write('\n')
        f.close()
    except:
        print "ERROR write to file " + fname
    return
#===================================================
def lib_evaluateAction( action):
	print action
#===================================================
def lib_readConfiguration(confile,c1):
	try:
		fh = open(confile, 'r')
		for line in fh:
			print line
			word = line.split()
			if word[0] == 'c_url':
				c1.c_url         = word[1]
			if word[0] == 'c_app':
				c1.c_server_app     = word[1]
			if word[0] == 'c_period':
				c1.c_period         = word[1]
			if word[0] == 'c_hw':
				c1.c_hw             = word[1]
			if word[0] == 'c_wrap':
				c1.c_wrap           = word[1]
			if word[0] == 'c_topic1':
				c1.c_topic1         = word[1]
			if word[0] == 'c_topic2':
				c1.c_topic2         = word[1]
			if word[0] == 'c_topic3':
				c1.c_topic3         = word[1]
			if word[0] == 'c_action1':
				c1.c_action1         = word[1]
			if word[0] == 'c_action2':
				c1.c_action2         = word[1]
			if word[0] == 'c_action3':
				c1.c_action3         = word[1]
			if word[0] == 'c_tag1':
				c1.c_tag1         = word[1]
			if word[0] == 'c_tag2':
				c1.c_tag2         = word[1]
			if word[0] == 'c_tag3':
				c1.c_tag3         = word[1]
				
			# Heater algorithm
			if word[0] == 'c_mintemp':
				c1.c_mintemp          = word[1]
			if word[0] == 'c_maxtemp':
				c1.c_maxtemp          = word[1]
			if word[0] == 'c_minheat':
				c1.c_minheat          = word[1]
			if word[0] == 'c_maxheat':
				c1.c_maxheat          = word[1]
			if word[0] == 'c_x_0':
				c1.c_x_0              = word[1]
			if word[0] == 'c_y_0':
				c1.c_y_0              = word[1]
			if word[0] == 'c_relax':
				c1.c_relax            = word[1]
			if word[0] == 'c_minsmoke':
				c1.c_minsmoke         = word[1]
			if word[0] == 'c_minsteps':
				c1.c_minsteps         = word[1]
			if word[0] == 'c_maxsteps':
				c1.c_maxsteps         = word[1]
			if word[0] == 'c_defsteps':
				c1.c_defsteps         = word[1]
			if word[0] == 'c_maxenergy':
				c1.c_maxenergy        = word[1]
			
			# Database access
			if word[0] == 'c_dbhost':
				c1.c_dbhost         = word[1]
			if word[0] == 'c_dbname':
				c1.c_dbname         = word[1]
			if word[0] == 'c_dbuser':
				c1.c_dbuser         = word[1]
			if word[0] == 'c_dbpassword':
				c1.c_dbpassword      = word[1]
				
		fh.close()
	except:
		fh = open(confile, 'w')
		fh.write('c_url      gow.simuino.com\n')
		fh.write('c_server   gowServer.php\n')
		fh.write('c_period   10\n')
		fh.write('c_hw       python\n')
		fh.write('c_wrap     999999\n')
		fh.write('c_topic1   topic1\n')
		fh.write('c_topic2   topic2\n')
		fh.write('c_topic3   topic3\n')
		fh.write('c_tag1     tag1\n')
		fh.write('c_tag2     tag2\n')
		fh.write('c_tag3     tag3\n')
		
		fh.write('c_mintemp      -7\n')
		fh.write('c_maxtemp      15\n')
		fh.write('c_minheat      25\n')
		fh.write('c_maxheat      40\n')
		fh.write('c_x_0          0\n')
		fh.write('c_y_0          36\n')
		fh.write('c_relax        4.0\n')
		fh.write('c_minsmoke     27\n')
		fh.write('c_minsteps     5\n')
		fh.write('c_maxsteps     40\n')
		fh.write('c_defsteps     30\n')
		fh.write('c_maxenergy    4.0\n')
		
		fh.write('c_dbhost       192.168.1.85\n')
		fh.write('c_dbname       gow\n')
		fh.write('c_dbuser       folke\n')
		fh.write('c_dbpassword   something\n')
		
		fh.close()
	return
#=============================================
def lib_readJsonMeta(url,par):
#=============================================
    r = urllib2.urlopen(url)
    j = json.load(r)
    x =  j['gow'][par]
    return x
#=============================================
def lib_readJsonPayload(url,par):
#=============================================
    r = urllib2.urlopen(url)
    j = json.load(r)
    x =  j['gow']['payload'][par]
    return x
#===================================================
def lib_publish(c1, itopic, ipayload, n ):
#===================================================
	url = c1.c_gs_url
	server = c1.c_server_app
	data = {}
	# meta data
	data['do']     = 'data'
	data['topic']  = itopic
	data['no']     = n
	data['wrap']   = c1.c_wrap
	data['ts']     = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
	data['period'] = c1.c_period
	data['hw']     = c1.c_hw
	# payload
	data['payload'] = ipayload

	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values
	print req
	try:
		response = urllib2.urlopen(req)
		the_page = response.read()
		print 'Message to ' + itopic + ': ' + the_page
		evaluateAction(the_page)
	except urllib2.URLError as e:
		print e.reason

#===================================================
def lib_placeOrder(c1, itopic, iaction, itag ):
#===================================================
	url = c1.c_gs_url
	server = c1.c_server_app
	data = {}
	data['do']     = 'action'
	data['topic']  = itopic
	data['order']  = iaction
	data['tag']    = itag
	values = urllib.urlencode(data)
	req = 'http://' + url + '/' + server + '?' + values
	print req
	try:
		response = urllib2.urlopen(req)
	except urllib2.URLError as e:
		print e.reason
#=============================================
def gowMysqlInsert(c1,cr,xTable,xPar,xValue):
    db = MySQLdb.connect(host=c1.c_dbhost,user=c1.c_dbuser,db=c1.c_dbname)
    cursor = db.cursor()
    if cr == 1:
        sql = "CREATE TABLE IF NOT EXISTS " + xTable + " (id int(11) NOT NULL AUTO_INCREMENT,value float,ts timestamp, PRIMARY KEY (id))"
        cursor.execute(sql)
    sql = "INSERT INTO "+ xTable + " (`id`, " + xPar + ", `ts`) VALUES (NULL," + str(xValue) + ", CURRENT_TIMESTAMP)"
    cursor.execute(sql)
    db.commit()
    db.close()

#===================================================
# End of file
#===================================================
