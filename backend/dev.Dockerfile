# please change :
# password.txt with your desire filename
# password_admin with your desire password admin
# 8000 with your available port

FROM ubuntu:20.04

ARG UNAME=py4web
ARG UID=1000
ARG GID=1000

ARG user=py4web
ARG password=none

RUN apt update && apt install -y git python3.8 python3-pip memcached

RUN update-alternatives --install /usr/bin/python python `which python3.8` 1
RUN update-alternatives --config python

RUN service memcached restart

RUN echo "TEST: ${GID} - ${UNAME}"

RUN groupadd -g ${GID} -o ${UNAME}
RUN useradd -l -o -m -u $UID -g $GID -s /bin/bash $UNAME

# RUN groupadd -r $user && useradd -m -r -g $user $user

RUN python3.8 -m pip install -U py4web==v1.20220222.1

COPY py-alert-system /home/$user/py-alert-system
RUN cd /home/$user/py-alert-system && \
    cp .env-template.env .env && \
    pip uninstall --yes alertsystem && \
    make package && pip install dist/alertsystem-0.0.0-py3-none-any.whl

RUN mkdir -p /home/$user/apps/emergenze/emergenze_uploads
RUN chown $user:$user -R /home/$user/apps
RUN chmod ug+rwx -R /home/$user/apps

USER $user

#COPY mypassword.txt /home/$user/

#RUN cd /home/$user/ && py4web setup --yes apps && py4web set_password /home/py4web/mypassword.txt

RUN cd /home/$user/ && py4web setup --yes apps

#COPY password.txt /home/$user/

RUN cd /home/$user/ && \
    if [ "$password" = "none" ]; then echo "no admin"; else py4web set_password < "$password"; fi

COPY requirements.txt /

USER root

RUN  pip install -r requirements.txt

USER $user

#RUN cd /home/$user/apps/ && mkdir emergenze

#COPY Emergenze-Verbatel /home/$user/apps/emergenze

#USER $user

EXPOSE 8000

WORKDIR /home/$user/

#USER root

#CMD py4web run --password_file password.txt --host 0.0.0.0 --port 8000 apps
