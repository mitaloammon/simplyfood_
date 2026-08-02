import type { CustomerDto } from '@/modules/customers/api/CustomerApi';
import { CustomerApi } from '@/modules/customers/api/CustomerApi';
import { OrderApi, type OrderDto, type OrderManagementFilters, type OrderManagementMeta, type OrderManagementRow, type OrderTimelineEntry } from '../api/OrderApi';

export class OrderManagementService {
  private readonly orderApi: OrderApi;
  private readonly customerApi: CustomerApi;

  constructor(orderApi?: OrderApi, customerApi?: CustomerApi) {
    this.orderApi = orderApi ?? new OrderApi();
    this.customerApi = customerApi ?? new CustomerApi();
  }

  async loadOrders(filters: OrderManagementFilters): Promise<{ rows: OrderManagementRow[]; meta: OrderManagementMeta }> {
    const response = await this.orderApi.getManagement(filters);

    return {
      rows: response.data.data || [],
      meta: response.data.meta,
    };
  }

  async loadOrderDetails(orderId: number): Promise<OrderDto> {
    const response = await this.orderApi.getById(orderId);
    return response.data.data;
  }

  async loadTimeline(orderId: number): Promise<OrderTimelineEntry[]> {
    const response = await this.orderApi.getTimeline(orderId);
    return response.data.data || [];
  }

  async loadCustomers(): Promise<CustomerDto[]> {
    const response = await this.customerApi.getAll();
    return response.data.data || [];
  }

  async associateCustomer(orderId: number, customerId: number): Promise<OrderDto> {
    const response = await this.orderApi.associateCustomer(orderId, customerId);
    return response.data.data;
  }

  async changeStatus(orderId: number, status: string): Promise<OrderDto> {
    const response = await this.orderApi.changeStatus(orderId, status);
    return response.data.data;
  }
}
