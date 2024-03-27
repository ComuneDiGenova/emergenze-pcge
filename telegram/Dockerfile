FROM python:3.8-slim as common

ARG UNAME
# ARG BOT_TOKEN
ARG UID
ARG GID

RUN groupadd -g $GID -o $UNAME
RUN useradd -o -m -u $UID -g $GID -s /bin/bash $UNAME

RUN apt-get update
# RUN apt-get install bash-completion -y

USER $UNAME

# set the working directory in the container
WORKDIR /home/$UNAME

RUN python3 -m pip install --upgrade pip

# FROM common as development
COPY ./emergenze ./emergenze
RUN python3 -m pip install -U -e ./emergenze
COPY ./.env ./.env
CMD python3 -m emergenze.emergenze.bot
# CMD sleep infinity
