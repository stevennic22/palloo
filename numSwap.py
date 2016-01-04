#!C:/Python27/python.exe
# -*- coding: utf-8 -*-
import os, re, string, sys, getopt, struct, socket
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import Select

def errPrint():
	print '-h, --help'
	print '	prints this help command'
	print '-l, --line='
	print '	input phone line to change phones'
	print '	must be home/office/mobile/voicemail'

def setLine(pLine):
	#Assigns driver to Firefox or Chrome (chromedriver must be installed in a folder specified on the path)
	#Firefox does not require an external application to function and works by default with Selenium installed
	driver = webdriver.Firefox() #webdriver.Chrome('C:\Python27\chromedriver.exe')

	#Loads Halloo login page (to ensure you actually log in)
	driver.get("https://secure1.halloo.com/sign-in/")

	#print driver.title

	#Login fields for Halloo. Can use just email and password.
	#If email not given, ucomp must be sleepex
	#nameInput = driver.find_element_by_name("uname")
	#nameInput.send_keys("Your Extension Name/User Name")

	#Finds login fields on Halloo Login page
	compInput = driver.find_element_by_name("ucomp")
	passInput = driver.find_element_by_name("upass")

	#Types login information on page
	compInput.send_keys("") #Insert your email address
	passInput.send_keys("") #Insert your pin code

	#Submits information
	passInput.submit()

	if "Sign-In" in driver.title:
		#Exits if statement because login failed to Halloo
		#print "Login to Halloo failed. Check login information" + "<br>"
		#Quits selenium driver
		driver.quit()
	else:
		fwdLine = Select(driver.find_element_by_id("cFwdLine"))
		fwdLine.select_by_value(pLine)
		driver.quit()
		exit()
	
def main(argv):
	try:
		opts, args = getopt.getopt(argv,"hl:", ["help", "line="])
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
		elif opt in ("-l", "--line"):
			lineToSet = arg
			if (string.lower(lineToSet) == string.lower("Mobile") or string.lower(lineToSet) == string.lower("Office") or string.lower(lineToSet) == string.lower("Home")):
				setLine(string.capwords(lineToSet))
			elif (string.lower(lineToSet) == string.lower("Voicemail")):
				setLine("0")
			else:
				errPrint()

if __name__ == '__main__':
    main(sys.argv[1:])