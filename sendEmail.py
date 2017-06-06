#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os, sys, getopt, smtplib, logging, datetime

logFileDir = "LOGS"

if(os.path.isdir(logFileDir) == False):
  os.makedirs(logFileDir)
logFileName = os.path.normpath(logFileDir + "/" + os.path.splitext(__file__)[0] + datetime.datetime.now().strftime("%y%m%d%H%M%S") + ".LOG")

log = logging.getLogger(__name__)
log.setLevel(logging.INFO)
handler = logging.FileHandler(logFileName)
formatter = logging.Formatter("[%(asctime)s] '%(levelname)s': {%(message)s}")
handler.setFormatter(formatter)
log.addHandler(handler)

def errPrint():
  helpMsg = '''-h, --help
   prints this help command
-r, --recipient
   attaches new recipient to email
-m, --msg
   attaches email body content
-t, --title
   attaches new email title'''
  print helpMsg
  log.info(helpMsg)

def sendingMail(messageToSend):
  try:
    import json
    with open('..' + os.sep + 'extensions.json', 'r') as eFile:
      extensionInfo = json.load(eFile)

    # Import the email modules we'll need
    from email.mime.text import MIMEText
    msg = MIMEText(messageToSend["msg"])

    msg['Subject'] = messageToSend["subject"]
    msg['From'] = extensionInfo["palloo"]["email"]["address"]
    msg['To'] = messageToSend["recipient"]

    # Send the message via our Gmail server, but don't include the
    # envelope header.
    s = smtplib.SMTP('smtp.gmail.com', 587)
    s.starttls()
    s.login(msg['From'], extensionInfo["palloo"]["email"]["key"])
    s.sendmail(msg['From'], msg['To'], msg.as_string())
    s.quit()
    log.info("Email sent.")
  except smtplib.SMTPRecipientsRefused, e:
    log.error(e)
    sys.exit(1)
  except:
    log.error("Unexpected error: ")
    log.error(sys.exc_info()[0])
    log.error(sys.exc_info()[1])
    sys.exit(1)
  
def main(argv):
  msgSetup = {
    "recipient": "example@example.com",
    "subject": "No Subject",
    "msg": "Hello World"
  }
  log.info("Starting to parse arguments")
  try:
    opts, args = getopt.getopt(argv,"hr:m:t:", ["help", "recipient=", "msg=", "title="])
    if not opts:
      # Return proper usage of script if in error
      log.warning("No arguments provided. Printing help message and closing.")
      errPrint()
      exit()
  except getopt.GetoptError:
    # Return proper usage of script if in error
    log.warning("No arguments provided. Printing help message and closing.")
    errPrint()
    sys.exit(2)
  for opt, arg in opts:
    if opt in ("-h", "--help"):
      # Return proper usage of script if in error
      log.info("Help message requested. Printing help message and closing.")
      errPrint()
      sys.exit(2)
    elif opt in ("-r", "--recipient"):
      msgSetup["recipient"] = arg
    elif opt in ("-m", "--msg"):
      msgSetup["msg"] = arg
    elif opt in ("-t", "--title"):
      msgSetup["subject"] = arg

  log.info(msgSetup)
  log.info("Attempting to send email.")
  sendingMail(msgSetup)
  
if __name__ == '__main__':
    main(sys.argv[1:])