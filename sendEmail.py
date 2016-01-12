#!C:/Python27/python.exe
# -*- coding: utf-8 -*-
import os, re, string, sys, getopt, struct, socket, smtplib

msgSetup = ["","",""]

def errPrint():
	print '-h, --help'
	print '	prints this help command'
	
def arrayAddition(arg,i):
	global msgSetup
	msgSetup[i]=arg
	return

def sendingMail():
	global msgSetup
	# Import the email modules we'll need
	from email.mime.text import MIMEText
	msg = MIMEText(msgSetup[0])

	# me == the sender's email address
	# you == the recipient's email address
	if msgSetup[2] == "":
		msg['Subject'] = "No Subject"
	else:
		msg['Subject'] = msgSetup[2]
	msg['From'] = "" #Put from email address
	msg['To'] = msgSetup[1]

	# Send the message via our own SMTP server, but don't include the
	# envelope header.
	s = smtplib.SMTP('smtp.gmail.com', 587)
	s.starttls()
	s.login(msg['From'], "") #Put application specific password
	s.sendmail(msg['From'], msg['To'], msg.as_string())
	s.quit()
	
def main(argv):
	global msgSetup
	try:
		opts, args = getopt.getopt(argv,"hr:m:t:", ["help", "recipient=", "msg=", "title="])
		if not opts:
			# Return proper usage of script if in error
			errPrint()
	except getopt.GetoptError:
		# Return proper usage of script if in error
		errPrint()
		sys.exit(2)
	for opt, arg in opts:
		if opt in ("-h", "--help"):
			# Return proper usage of script if in error
			errPrint()
			sys.exit(2)
		elif opt in ("-r", "--recipient"):
			arrayAddition(arg,1)
		elif opt in ("-m", "--msg"):
			arrayAddition(arg,0)
		elif opt in ("-t", "--title"):
			arrayAddition(arg,2)

	sendingMail()
	
if __name__ == '__main__':
    main(sys.argv[1:])