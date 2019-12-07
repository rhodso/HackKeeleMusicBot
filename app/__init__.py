from flask import Flask
from app import config
from app.config import Config

app = Flask(__name__)
app.config.from_object(Config)

from app import routes

@app.route('/crash')
def main():
    raise Exception()

app.run(debug=True)
