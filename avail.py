#!C:/Python27/python.exe
# -*- coding: utf-8 -*-
import os, re, string, sys, getopt, struct, socket
from time import sleep
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

def main():
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
		availLine = driver.find_element_by_xpath("//*[contains(@title, 'Click to toggle availability')]")
		availLine.click()
		sleep(10)
		driver.quit()
		exit()

if __name__ == '__main__':
    main()