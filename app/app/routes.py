from flask import render_template, flash, redirect, url_for, request, session

from app import app
from app.forms import UsernameForm, IDForm


@app.route('/', methods=['GET', 'POST'])
def index():
    form = UsernameForm()

    videos = [
        {
            'votes': 1,
            'UID': {'Username': 'RoryGee'},
            'VideoID': 'OWc1jaycOlQ'
        },
        {
            'votes': 1,
            'UID': {'Username': 'Rhodso'},
            'VideoID': 'OWc1jaycOlQ'
        }
    ]
    currentVideo = next(iter(videos))
    return render_template('index.html', title='HACK Keele | Music Player', form=form,videos=videos, currentVideo=currentVideo, name='RoryGee')


@app.route('/requestSong')
def requestSong():
    form = IDForm()
    return render_template('request.html', title='HACK Keele | Music Player', form=form)