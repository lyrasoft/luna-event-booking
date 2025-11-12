import { data, useDisableOnSubmit, useFormValidation, useUniDirective } from '@windwalker-io/unicorn-next';

const formSelector = '#attend-form';

useFormValidation().then(() => useDisableOnSubmit(formSelector));

const canAttend = data<boolean>('can.attend') ?? true;
const form = document.querySelector<HTMLFormElement>(formSelector)!;
const submitButton = form.querySelector<HTMLButtonElement>('[data-task=submit]')!;

useUniDirective('plan-quantity', {
  mounted(el) {
    el.addEventListener('change', () => {
      if (!canAttend) {
        submitButton.disabled = true;
        return;
      }

      submitButton.disabled = calcTotalQuantity() === 0;
    });
  }
});

function calcTotalQuantity() {
  let total = 0;
  for (const input of form.querySelectorAll('[uni-plan-quantity]')) {
    const value = parseInt((input as HTMLInputElement).value, 10) || 0;
    total += value;
  }

  return total;
}
