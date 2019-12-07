#!/usr/bin/python3

import re

testUsername = "fuck"

swearFile = open('app/badwords.txt', 'r')
swearList = swearFile.readlines()
c = 0
for swear in swearList:
    swear = re.sub("\s", "", swear)
    for w in testUsername.split():
        if(w == swear):
            c = c + 1
            break
if c > 0:
    print("oShit")
else:
    print("you're good")
swearFile.close()

