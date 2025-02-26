import { ApolloClient, InMemoryCache } from '@apollo/client';

const client = new ApolloClient({
  uri: 'http://52.87.237.144/api/',
  cache: new InMemoryCache(),
});

export default client;

