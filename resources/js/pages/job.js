// import '../legacy/subscribe';
import Router from '../libs/router';

(() => {
  let r = new Router();

  r.on('/Praca/Application/*', () => {
    require('./job/application');
  })
  .on('/Praca/Payment/*', () => {
    require('./job/payment');
  })
  .on('/Praca/Oferta', () => {
    require('./job/business');
  })
  .on(['/Praca', '/Praca/Miasto/*', '/Praca/Technologia/*', '/Praca/Zdalna', '/Praca/Firma/*'], () => {
    require('./job/homepage');
  })
  .on('/Praca/\\d+\\-*', () => {
    require('./job/offer');
  })
  .on(['/Praca/Submit/*'], () => {
    import('./job/submit');
  });

  r.resolve();
})();
