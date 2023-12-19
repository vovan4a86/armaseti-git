import 'focus-visible';
import './modules';
import './plugins';
import { maskedInputs } from './modules/inputMask';

maskedInputs({
  phoneSelector: 'input[name="phone"]',
  emailSelector: 'input[name="email"]'
});
