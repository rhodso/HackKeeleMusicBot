from flask_wtf import FlaskForm

from wtforms import StringField, SubmitField, ValidationError
from wtforms.validators import DataRequired

import re


class IDForm(FlaskForm):
    videoID = StringField('Video ID: ', validators=[DataRequired()])
    submit = SubmitField('Submit')


class UsernameForm(FlaskForm):
    def validateName(self, field):

        swearFile = open('app/badwords', 'r')
        swearList = swearFile.readlines()
        for swear in swearList:
            swearRegex = r""+swear
            if re.search(swearRegex, str(field), re.IGNORECASE):
                raise ValidationError('Choose another name...')
        swearFile.close()

        nameFile = open('app/currentUsers.txt', 'r')
        nameList = nameFile.readlines()
        for name in nameList:
            if field == name:
                raise ValidationError('Choose another name...')
        nameFile.close()

    userName = StringField('Enter your preferred name: ', validators=[DataRequired(), validateName])
    submit = SubmitField('Submit')