const SELECTOR = '.js-phone, [data-phone-mask="true"]';
const DATA_FLAG = 'phoneMaskInitialized';

const getInputNumbersValue = (input) => {
  if (!input) {
    return '';
  }
  return String(input.value || '').replace(/\D/g, '');
};

const sanitizePhoneValue = (value) => {
  return String(value || '').replace(/\D/g, '');
};

const formatFromDigits = (digits) => {
  if (!digits) {
    return '';
  }

  let normalized = digits;
  let formatted = '';

  if (["7", "8", "9"].indexOf(normalized[0]) > -1) {
    if (normalized[0] === '9') {
      normalized = '7' + normalized;
    }

    const firstSymbols = normalized[0] === '8' ? '8' : '+7';
    formatted = `${firstSymbols} `;

    if (normalized.length > 1) {
      formatted += `(${normalized.substring(1, 4)}`;
    }
    if (normalized.length >= 5) {
      formatted += `) ${normalized.substring(4, 7)}`;
    }
    if (normalized.length >= 8) {
      formatted += `-${normalized.substring(7, 9)}`;
    }
    if (normalized.length >= 10) {
      formatted += `-${normalized.substring(9, 11)}`;
    }
  } else {
    formatted = `+${normalized.substring(0, 16)}`;
  }

  return formatted;
};

const onPhonePaste = (event) => {
  const input = event.target;
  const inputNumbersValue = getInputNumbersValue(input);
  const pasted = event.clipboardData || window.clipboardData;

  if (!pasted) {
    return;
  }

  const pastedText = pasted.getData('Text');
  if (/\D/g.test(pastedText)) {
    input.value = inputNumbersValue;
    event.preventDefault();
  }
};

const onPhoneInput = (event) => {
  const input = event.target;
  let inputNumbersValue = getInputNumbersValue(input);
  const selectionStart = input.selectionStart;

  if (!inputNumbersValue) {
    input.value = '';
    return;
  }

  if (selectionStart !== null && input.value.length !== selectionStart) {
    if (event.data && /\D/g.test(event.data)) {
      input.value = inputNumbersValue;
    }
    return;
  }

  input.value = formatFromDigits(inputNumbersValue);
};

const onPhoneKeyDown = (event) => {
  const input = event.target;
  const inputValue = sanitizePhoneValue(input.value || '');

  if (event.key === 'Backspace' || event.keyCode === 8) {
    if (inputValue.length <= 1) {
      input.value = '';
    }
  }
};

const initInput = (input) => {
  if (!input || input.dataset[DATA_FLAG] === 'true') {
    return;
  }

  input.addEventListener('keydown', onPhoneKeyDown);
  input.addEventListener('input', onPhoneInput, false);
  input.addEventListener('paste', onPhonePaste, false);
  input.dataset[DATA_FLAG] = 'true';

  const formatted = formatFromDigits(sanitizePhoneValue(input.value));
  if (formatted && formatted !== input.value) {
    input.value = formatted;
    let event;
    if (typeof Event === 'function') {
      event = new Event('input', { bubbles: true });
    } else if (document && document.createEvent) {
      event = document.createEvent('HTMLEvents');
      event.initEvent('input', true, false);
    }
    if (event) {
      input.dispatchEvent(event);
    }
  }
};

export const applyPhoneMask = (root) => {
  if (typeof document === 'undefined') {
    return;
  }

  const context = root && root.querySelectorAll ? root : document;
  const inputs = context.querySelectorAll(SELECTOR);
  inputs.forEach((input) => initInput(input));
};

export const formatPhoneValue = (value) => {
  return formatFromDigits(sanitizePhoneValue(value));
};

export const getPhoneDigits = (value) => {
  return sanitizePhoneValue(value);
};

if (typeof document !== 'undefined') {
  const runPhoneMask = () => applyPhoneMask(document);

  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    runPhoneMask();
  } else {
    document.addEventListener('DOMContentLoaded', runPhoneMask);
  }

  document.addEventListener('devob:form-phone-mask-request', runPhoneMask);
}

if (typeof window !== 'undefined') {
  window.DevobPhoneMask = window.DevobPhoneMask || {};
  window.DevobPhoneMask.apply = applyPhoneMask;
  window.DevobPhoneMask.format = formatPhoneValue;
  window.DevobPhoneMask.getDigits = getPhoneDigits;
}

export default {
  apply: applyPhoneMask,
  format: formatPhoneValue,
  getDigits: getPhoneDigits
};
