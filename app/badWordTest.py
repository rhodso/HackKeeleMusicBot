#!/usr/bin/python3

testUsername = "fuck"

swearFile = open('badwords.txt', 'r')
swearList = swearFile.readlines()
for swear in swearList:
    c = 0
    for w in testUsername.split():
        if(w == swear):
            c = c + 1
    if c > 0:
        print("oShit")
    else:
        print("you're good")
swearFile.close()

