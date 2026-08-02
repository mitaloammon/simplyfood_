import { createPinia, setActivePinia } from 'pinia';
import { flushPromises, mount } from '@vue/test-utils';
import { defineComponent, h, nextTick, ref } from 'vue';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import DashboardPage from '@/modules/dashboard/pages/DashboardPage.vue';

const loadMetrics = vi.fn();

const customerApiMock = {
  getAll: vi.fn(),
};

const productApiMock = {
  getActive: vi.fn(),
  getQuickCreateOptions: vi.fn(),
  create: vi.fn(),
};

const orderApiMock = {
  create: vi.fn(),
};

const createCustomerServiceMock = {
  execute: vi.fn(),
};

vi.mock('@/modules/dashboard/composables/useDashboardMetrics', () => ({
  useDashboardMetrics: () => ({
    loading: ref(false),
    errorMessage: ref(''),
    metrics: ref([]),
    user: ref({ name: 'ana', email: 'ana@simplyfood.test', id: 1, role: 'admin' }),
    loadMetrics,
  }),
}));

vi.mock('@/modules/customers/api/CustomerApi', () => ({
  CustomerApi: class CustomerApi {
    getAll = customerApiMock.getAll;
  },
}));

vi.mock('@/modules/customers/services/CreateCustomerService', () => ({
  CreateCustomerService: class CreateCustomerService {
    execute = createCustomerServiceMock.execute;

    constructor(_api: unknown) {}
  },
}));

vi.mock('@/modules/orders/api/OrderApi', () => ({
  OrderApi: class OrderApi {
    create = orderApiMock.create;
  },
}));

vi.mock('@/modules/products/api/ProductApi', () => ({
  ProductApi: class ProductApi {
    getActive = productApiMock.getActive;
    getQuickCreateOptions = productApiMock.getQuickCreateOptions;
    create = productApiMock.create;
  },
}));

const CustomerFormStub = defineComponent({
  name: 'CustomerForm',
  emits: ['submit'],
  setup(_, { slots }) {
    return () => h('div', { 'data-testid': 'customer-form-stub' }, slots.default?.());
  },
});

const ProductQuickCreateModalStub = defineComponent({
  name: 'ProductQuickCreateModal',
  props: {
    modelValue: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:modelValue', 'save'],
  setup(props, { emit }) {
    return () => {
      if (!props.modelValue) {
        return null;
      }

      return h('div', { 'data-testid': 'product-quick-create-modal' }, [
        h('button', {
          type: 'button',
          'data-testid': 'save-product',
          onClick: () => emit('save', {
            nome: 'Coxinha Especial',
            preco: 12.5,
            ativo: true,
            controla_estoque: false,
            produzido_cozinha: true,
            delivery: true,
            balcao: true,
            mesa: true,
            retirada: true,
            unidade: 'UN',
          }),
        }, 'Salvar produto'),
      ]);
    };
  },
});

const mountDashboard = () =>
  mount(DashboardPage, {
    global: {
      stubs: {
        CustomerForm: CustomerFormStub,
        ProductQuickCreateModal: ProductQuickCreateModalStub,
      },
    },
  });

beforeEach(() => {
  setActivePinia(createPinia());
  localStorage.clear();
  loadMetrics.mockClear();
  customerApiMock.getAll.mockReset();
  productApiMock.getActive.mockReset();
  productApiMock.getQuickCreateOptions.mockReset();
  productApiMock.create.mockReset();
  orderApiMock.create.mockReset();
  createCustomerServiceMock.execute.mockReset();

  productApiMock.getQuickCreateOptions.mockResolvedValue({
    data: {
      data: {
        categories: [],
        units: [],
        defaults: {
          ativo: true,
          controla_estoque: false,
          produzido_cozinha: true,
          delivery: true,
          balcao: true,
          mesa: true,
          retirada: true,
          unidade: 'UN',
        },
      },
    },
  });
});

describe('DashboardPage', () => {
  it('abre o modal de cadastro rapido ao clicar em Novo Produto', async () => {
    customerApiMock.getAll.mockResolvedValue({ data: { data: [{ id: 7, name: 'Cliente Base' }] } });
    productApiMock.getActive.mockResolvedValue({ data: { data: [] } });

    const wrapper = mountDashboard();

    await flushPromises();

    await wrapper.findAll('button.action-card')[1].trigger('click');
    await flushPromises();

    expect(wrapper.find('[data-testid="product-quick-create-modal"]').exists()).toBe(true);
  });

  it('preserva o estado do pedido ao cadastrar um produto no meio do fluxo', async () => {
    customerApiMock.getAll.mockResolvedValue({ data: { data: [{ id: 7, name: 'Cliente Base' }] } });
    productApiMock.getActive
      .mockResolvedValueOnce({
        data: {
          data: [
            { id: 11, nome: 'Hamburguer', preco_venda: 19.9, preco: 19.9 },
          ],
        },
      })
      .mockResolvedValueOnce({
        data: {
          data: [
            { id: 11, nome: 'Hamburguer', preco_venda: 19.9, preco: 19.9 },
            { id: 99, nome: 'Coxinha Especial', preco_venda: 12.5, preco: 12.5 },
          ],
        },
      });

    productApiMock.create.mockResolvedValue({
      data: {
        data: {
          id: 99,
          nome: 'Coxinha Especial',
          preco_venda: 12.5,
          preco: 12.5,
        },
      },
    });

    const wrapper = mountDashboard();

    await flushPromises();

    await wrapper.findAll('button.action-card')[2].trigger('click');
    await flushPromises();

    const quantityInput = wrapper.get('input[placeholder="Quantidade"]');
    await quantityInput.setValue('3');

    await wrapper.findAll('button.btn-inline')[0].trigger('click');
    await flushPromises();

    expect(wrapper.find('[data-testid="product-quick-create-modal"]').exists()).toBe(true);

    await wrapper.get('[data-testid="save-product"]').trigger('click');
    await flushPromises();
    await nextTick();

    expect(wrapper.find('[data-testid="product-quick-create-modal"]').exists()).toBe(false);
    expect((wrapper.get('input[placeholder="Quantidade"]').element as HTMLInputElement).value).toBe('3');
    expect((wrapper.get('select').element as HTMLSelectElement).value).toBe('99');
    expect(orderApiMock.create).not.toHaveBeenCalled();
  });
});