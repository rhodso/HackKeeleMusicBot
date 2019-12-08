from flask_wtf import FlaskForm

from wtforms import StringField, SubmitField, ValidationError
from wtforms.validators import DataRequired

import re


class IDForm(FlaskForm):
    videoID = StringField('Video ID: ', validators=[DataRequired()])
    submit = SubmitField('Submit')



class UsernameForm(FlaskForm):
    exceptionMessage = StringField('')

    def validateName(self, field):
        
        fieldInput = str(field).split("\"")
        testUsername = fieldInput[7]
        testUsername = re.sub(r"[^(A-Z)|(a-z)|(0-9)]*", "", testUsername)
        testUsername = re.sub("\n", "", testUsername)

        if(len(testUsername) > 32):
            raise ValidationError("Name too long! (Max 32 chars)\nChoose another name")

        swearFile = open('app/badwords.txt', 'r')
        swearList = swearFile.readlines()
        
        c = 0
        for swear in swearList:
            swear = re.sub(r"\s", "", swear)
            for w in testUsername.split("\""):
                if(w == swear):
                    c = c + 1
                    break
        if(c > 0):
            raise ValidationError('Banned word!\nChoose another name')
        swearFile.close()
        
        nameFile = open('app/currentUsers.txt', 'r')
        nameList = nameFile.readlines()
        c = 0
        for name in nameList:
            name = re.sub(r"[^(A-Z)|(a-z)|(0-9)]*", "", name)
            for w in testUsername.split("\""):
                w = re.sub(r"[^(A-Z)|(a-z)|(0-9)]*", "", w)
                if(w == name):
                    c = c + 1
                    break
        if(c > 0):
            raise ValidationError('Name already taken!\nChoose another name')
        nameFile.close()

    userName = StringField('Enter your preferred name: ', validators=[DataRequired(), validateName])
    submit = SubmitField('Submit')