from flask_wtf import FlaskForm

from wtforms import StringField, SubmitField, ValidationError
from wtforms.validators import DataRequired


class IDForm(FlaskForm):
    videoID = StringField('Video ID: ', validators=[DataRequired()])
    submit = SubmitField('Submit')


class UsernameForm(FlaskForm):
    def validateName(self, field):
        #TODO add validation to make sure that it doesn't already exist
        #Also TODO: make badwords check irrespective of position (REGEX???)
        swearFile = open('badwords', 'r')
        swearList = swearFile.readlines()
        for swear in swearList():
            if field == swear:
                raise ValidationError('Choose another word...')
        swearList.close()

    userName = StringField('Enter your preferred name: ', validators=[DataRequired(), validateName])
    submit = SubmitField('Submit')