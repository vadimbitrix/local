(function (window) {
    'use strict';

    class PhoneMask {
        constructor() {
            this.getInputNumbersValue = (input) => (input.value || '').replace(/\D/g, '');
        }

        // Получить чистое значение без маски
        unmask(value) {
            return (value || '').replace(/\D/g, '');
        }

        // Применить маску к input элементу
        applyMask(inputElement) {
            if (!inputElement || inputElement.dataset.phoneMaskApplied === 'true') {
                return;
            }

            const onPhonePaste = (event) => {
                const input = event.target;
                const inputNumbersValue = this.getInputNumbersValue(input);
                const pasted = event.clipboardData || window.clipboardData;
                if (!pasted) return;

                const pastedText = pasted.getData('Text');
                if (/\D/g.test(pastedText)) {
                    input.value = inputNumbersValue;
                }
            };

            const onPhoneInput = (event) => {
                const input = event.target;
                let inputNumbersValue = this.getInputNumbersValue(input);
                const selectionStart = input.selectionStart;
                let formattedInputValue = '';

                if (!inputNumbersValue) {
                    input.value = '';
                    return;
                }

                if (input.value.length !== selectionStart) {
                    if (event.data && /\D/g.test(event.data)) {
                        input.value = inputNumbersValue;
                    }
                    return;
                }

                if (["7", "8", "9"].indexOf(inputNumbersValue[0]) > -1) {
                    if (inputNumbersValue[0] === '9') {
                        inputNumbersValue = '7' + inputNumbersValue;
                    }
                    const firstSymbols = inputNumbersValue[0] === '8' ? '8' : '+7';
                    formattedInputValue = `${firstSymbols} `;
                    if (inputNumbersValue.length > 1) {
                        formattedInputValue += `(${inputNumbersValue.substring(1, 4)}`;
                    }
                    if (inputNumbersValue.length >= 5) {
                        formattedInputValue += `) ${inputNumbersValue.substring(4, 7)}`;
                    }
                    if (inputNumbersValue.length >= 8) {
                        formattedInputValue += `-${inputNumbersValue.substring(7, 9)}`;
                    }
                    if (inputNumbersValue.length >= 10) {
                        formattedInputValue += `-${inputNumbersValue.substring(9, 11)}`;
                    }
                } else {
                    formattedInputValue = `+${inputNumbersValue.substring(0, 16)}`;
                }

                input.value = formattedInputValue;
            };

            const onPhoneKeyDown = (event) => {
                const inputValue = (event.target.value || '').replace(/\D/g, '');
                if (event.keyCode === 8 && inputValue.length === 1) {
                    event.target.value = '';
                }
            };

            inputElement.addEventListener('keydown', onPhoneKeyDown);
            inputElement.addEventListener('input', onPhoneInput);
            inputElement.addEventListener('paste', onPhonePaste);

            // Сохраняем обработчики для последующего удаления
            inputElement.dataset.phoneMaskApplied = 'true';
            inputElement._phoneMaskHandlers = { onPhoneKeyDown, onPhoneInput, onPhonePaste };
        }

        // Удалить маску
        removeMask(inputElement) {
            if (!inputElement || !inputElement._phoneMaskHandlers) return;

            const handlers = inputElement._phoneMaskHandlers;
            inputElement.removeEventListener('keydown', handlers.onPhoneKeyDown);
            inputElement.removeEventListener('input', handlers.onPhoneInput);
            inputElement.removeEventListener('paste', handlers.onPhonePaste);

            inputElement.dataset.phoneMaskApplied = 'false';
            delete inputElement._phoneMaskHandlers;
        }

        // Валидация номера телефона
        validate(value) {
            const unmasked = this.unmask(value);
            return /^[78]\d{10}$/.test(unmasked);
        }

        // Получить форматированный номер с маской
        format(value) {
            let inputNumbersValue = this.unmask(value);

            if (!inputNumbersValue) {
                return '';
            }

            let formattedInputValue = '';

            if (["7", "8", "9"].indexOf(inputNumbersValue[0]) > -1) {
                if (inputNumbersValue[0] === '9') {
                    inputNumbersValue = '7' + inputNumbersValue;
                }
                const firstSymbols = inputNumbersValue[0] === '8' ? '8' : '+7';
                formattedInputValue = `${firstSymbols} `;
                if (inputNumbersValue.length > 1) {
                    formattedInputValue += `(${inputNumbersValue.substring(1, 4)}`;
                }
                if (inputNumbersValue.length >= 5) {
                    formattedInputValue += `) ${inputNumbersValue.substring(4, 7)}`;
                }
                if (inputNumbersValue.length >= 8) {
                    formattedInputValue += `-${inputNumbersValue.substring(7, 9)}`;
                }
                if (inputNumbersValue.length >= 10) {
                    formattedInputValue += `-${inputNumbersValue.substring(9, 11)}`;
                }
            } else {
                formattedInputValue = `+${inputNumbersValue.substring(0, 16)}`;
            }

            return formattedInputValue;
        }
    }

    // Экспортируем в глобальный объект
    window.PhoneMask = new PhoneMask();

})(window);
