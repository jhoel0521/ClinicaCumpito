import { initSwalConfirmInterceptor, initNotifyListener, initFlashToastListener } from './swal';
import { initNetworkResilience } from './network';
import { initImageCompression } from './image-compress';
import { initPrintLinks } from './print-link';
import { initLabOrderDraft } from './lab-draft';

initSwalConfirmInterceptor();
initNotifyListener();
initFlashToastListener();
initNetworkResilience();
initImageCompression();
initPrintLinks();
initLabOrderDraft();
