from flask import render_template, flash, redirect, url_for, request, session

from app import app
from app.forms import UsernameForm, IDForm


@app.route('/', methods=['GET', 'POST'])
def index():
    if 'username' in session:
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
        curVideo = next(iter(videos))
    else:
        return redirect('/register')
    return render_template('index.html', title='HACK Keele | Music Player', videos=videos, curVideo=curVideo, name=session.get('username'))


@app.route('/register', methods=['GET', 'POST'])
def register():
    form = UsernameForm()
    if form.validate_on_submit():
        session['username'] = form.userName.data
        return redirect('/')
    return render_template('register.html', title='HACK Keele | Music Player', form=form)


@app.route('/requestSong', methods=['GET', 'POST'])
def requestSong():
    form = IDForm()
    if form.validate_on_submit():
        return redirect('/')
    return render_template('request.html', title='HACK Keele | Music Player', form=form)