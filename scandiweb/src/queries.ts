import { gql } from "@apollo/client";

export const GET_CATEGORIES = gql`
  query GetCategories {
    categories {
      id
      name
    }
  }
`;

export const GET_PRODUCTS = gql`
  query GetProducts($category: String) {
    products(category: $category) {
      id
      name
      prices {
        amount
        currency {
          symbol
        }
      }
      gallery
      category {
        name
      }
      inStock
      attributes {
        id
        name
        type
        items {
          id
          value
          displayValue
        }
      }
    }
  }
`;

export const GET_PRODUCT_BY_ID = gql`
  query GetProductById($id: ID!) {
    product(id: $id) {
      id
      name
      description
      prices {
        amount
        currency {
          symbol
        }
      }
      category {
        name
      }
      gallery
      inStock
      attributes {
        id
        name
        type
        items {
          id
          value
          displayValue
        }
      }
    }
  }
`;

export const PLACE_ORDER = gql`
  mutation PlaceOrder($items: [OrderItemInput!]!) {
    placeOrder(items: $items) {
      success
      message
    }
  }
  
  input OrderItemInput {
    productId: ID!
    attributes: [AttributeInput!]!
    quantity: Int!
  }

  input AttributeInput {
    id: ID!
    value: String!
  }
`;