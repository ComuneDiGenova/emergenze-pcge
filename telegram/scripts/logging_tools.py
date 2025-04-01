import logging
import sys

# Courtesy of: https://github.com/web2py/py4web/blob/master/py4web/server_adapters/logging_utils.py#L6


def make_logger(name, loggers_info):
    """
    Abstraction layer on logging. Example of usage:

    from py4web.server_adapters.logging_utils import make_logger

    loggers_info = [
        "warning:warning.log",
        "info:info.log",
        "debug:debug.log:$(asctime)s > %(levelname)s > %(message)s",
    ]

    logger = make_logger("py4web:appname", loggers_info)
    """
    default_formatter = (
        "%(asctime)s - %(levelname)s - %(filename)s:%(lineno)d - %(message)s"
    )
    # reset loggers
    root = logging.getLogger(name)
    list(map(root.removeHandler, root.handlers))
    list(map(root.removeFilter, root.filters))
    for logger in loggers_info:
        logger += ":stderr" if logger.count(":") == 0 else ""
        logger += ":" if logger.count(":") == 1 else ""
        level, filename, formatter = logger.split(":", 2)
        if not formatter:
            formatter = default_formatter
        if filename in ("stdout", "stderr"):
            handler = logging.StreamHandler(getattr(sys, filename))
        else:
            handler = logging.FileHandler(filename)
        handler.setFormatter(logging.Formatter(formatter))
        handler.setLevel(getattr(logging, level.upper(), "DEBUG"))
        root.addHandler(handler)

    root.setLevel('DEBUG')
    return root
