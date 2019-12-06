from flask import render_template, flash, redirect, url_for, request, session

from app import app
from app.forms import UsernameForm, IDForm


@app.route('/', methods=['GET', 'POST'])
def index():
    form = UsernameForm()
    return render_template('index.html', title='HACK Keele | Music Player', form=form)


@app.route('/requestSong')
def requestSong():
    form = IDForm()
    return render_template('request.html', title='HACK Keele | Music Player',form=form)