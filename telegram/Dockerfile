FROM python:3.8-slim

ARG UNAME
ARG BOT_TOKEN
ARG UID=${UID}
ARG GID=${GID}

RUN groupadd -g $GID -o $UNAME
RUN useradd -o -m -u $UID -g $GID -s /bin/bash $UNAME

RUN apt-get update
# RUN apt-get install bash-completion -y

USER $UNAME

# set the working directory in the container
WORKDIR /home/$UNAME