import { initSwalConfirmInterceptor, initNotifyListener, initFlashToastListener } from './swal';
import { initNetworkResilience } from './network';
import { initImageCompression } from './image-compress';

initSwalConfirmInterceptor();
initNotifyListener();
initFlashToastListener();
initNetworkResilience();
initImageCompression();
