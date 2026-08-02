export const ORDERS_CHANGED_EVENT = 'orders:changed';

export const dispatchOrdersChanged = () => {
  window.dispatchEvent(new CustomEvent(ORDERS_CHANGED_EVENT));
};

export const subscribeOrdersChanged = (callback: () => void): (() => void) => {
  const handler = () => callback();
  window.addEventListener(ORDERS_CHANGED_EVENT, handler);

  return () => {
    window.removeEventListener(ORDERS_CHANGED_EVENT, handler);
  };
};
