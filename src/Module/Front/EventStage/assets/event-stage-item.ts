import { useDisableOnSubmit, useFormValidation } from '@windwalker-io/unicorn-next';

const formSelector = '#attend-form';

useFormValidation().then(() => useDisableOnSubmit(formSelector));
