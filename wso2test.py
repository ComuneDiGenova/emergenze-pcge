from . import evento
from . import settings
from verbatel import proxy

ee = evento.fetch(paginate=1)
evt = next(ee)



res = proxy.post('/eventi', data)
