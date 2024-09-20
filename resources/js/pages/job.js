import Router from '../libs/router.js';

new Router()
  .on(['/Praca/Application/*'], () => require('./job/application'))
  .on(['/Praca/Payment/*'], () => require('./job/payment'))
  .on(['/Praca/Oferta'], () => require('./job/business'))
  .on(['/Praca', '/Praca/Miasto/*', '/Praca/Technologia/*', '/Praca/Zdalna', '/Praca/Firma/*', '/Praca/Moje'], () => require('./job/homepage'))
  .on(['/Praca/\\d+\\-*'], () => require('./job/offer'))
  .on(['/Praca/Submit*', '/Praca/Renew/*'], () => import('./job/submit'))
  .resolve();
