import { ApolloClient, InMemoryCache } from '@apollo/client';

const client = new ApolloClient({
  uri: 'http://18.233.5.225/api/',
  cache: new InMemoryCache(),
});

export default client;

