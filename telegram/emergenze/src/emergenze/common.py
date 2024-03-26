import sys
import logging
from . import settings

def get_logger(app_name:str, loggers_conf:list[str]):
    """ """
    
    logger = logging.getLogger(app_name)
    formatter = logging.Formatter(
        "%(asctime)s - %(levelname)s - %(filename)s:%(lineno)d - %(message)s"
    )

    for item in loggers_conf:
        level, filename = item.split(":", 1)
        if filename in ("stdout", "stderr"):
            handler = logging.StreamHandler(getattr(sys, filename))
        else:
            handler = logging.FileHandler(filename)
        handler.setFormatter(formatter)
        logger.setLevel(getattr(logging, level.upper(), "DEBUG"))
        logger.addHandler(handler)

    return logger