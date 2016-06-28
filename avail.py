#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os, logging, datetime
from time import sleep
from selenium import webdriver
from selenium.common.exceptions import TimeoutException
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

if(os.path.isdir("LOGS") == False):
	os.makedirs("LOGS")
logFileName = os.path.normpath("LOGS\CalChecking") + datetime.datetime.now().strftime("%d%m%y%H%M%S") + ".LOG"
logging.basicConfig(format='%(levelname)s: %(message)s', filename=logFileName,level=logging.INFO)

def main():
  #Assigns driver to Firefox or Chrome (chromedriver must be installed in a folder specified on the path)
  #Firefox does not require an external application to function and works by default with Selenium installed
  logging.info("Starting webdriver.")
  driver = webdriver.Firefox() #webdriver.Chrome('C:\Python27\chromedriver.exe')

  #Loads Halloo login page (to ensure you actually log in)
  logging.info("Opening Halloo Sign-in page.")
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
  logging.info("Inserting username and password onto page.")
  compInput.send_keys("") #Insert your email address
  passInput.send_keys("") #Insert your pin code

  #Submits information
  logging.info("Attempting to log in.")
  passInput.submit()

  if "Sign-In" in driver.title:
    #Exits if statement because login failed to Halloo
    #print "Login to Halloo failed. Check login information" + "<br>"
    #Quits selenium driver
    logging.warning("Login failed. Closing.")
    driver.quit()
  else:
    logging.info("Successful login, toggling availability.")
    availLine = driver.find_element_by_xpath("//*[contains(@title, 'Click to toggle availability')]")
    availLine.click()
    sleep(10)
    driver.quit()
    exit()

if __name__ == '__main__':
    main()