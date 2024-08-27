FROM python:3.8-slim as common

ARG UNAME
ARG UID
ARG GID

RUN groupadd -g $GID -o $UNAME
RUN useradd -o -m -u $UID -g $GID -s /bin/bash $UNAME

RUN apt-get update

# Set the working directory in the container
WORKDIR /home/$UNAME

# Ensure the directory has correct permissions
COPY ./bots ./bots
RUN chown -R $UID:$GID ./bots

USER $UNAME

RUN python3 -m pip install --upgrade pip
RUN python3 -m pip install ./bots

COPY ./.env ./.env
CMD python3 -m common.emergenze.bot

