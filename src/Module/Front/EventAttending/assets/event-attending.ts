import { selectAll, useDisableOnSubmit, useFormValidation } from '@windwalker-io/unicorn-next';

const formSelector = '#attending-form';

useFormValidation().then(() => {
  useDisableOnSubmit(formSelector);
});

// Invoice Inputs
const carrierInput = document.querySelector<HTMLInputElement>('#input-order-invoice_data-carrier_code')!;
const invoiceTitleInput = document.querySelector<HTMLInputElement>('#input-order-invoice_data-title')!;
const invoiceVatInput = document.querySelector<HTMLInputElement>('#input-order-invoice_data-vat')!;

selectAll<HTMLInputElement>('[name="order[invoice_type]"]', (radio) => {
  radio.addEventListener('change', (e) => {
    const v = (e.target as HTMLInputElement).value;

    carrierInput.disabled = true;
    invoiceTitleInput.disabled = true;
    invoiceVatInput.disabled = true;

    if (v === 'personal') {
      carrierInput.disabled = false;
    } else {
      invoiceTitleInput.disabled = false;
      invoiceVatInput.disabled = false;
    }
  });
});
