import { ApolloClient, InMemoryCache } from '@apollo/client';
const client = new ApolloClient({
  uri: 'http://backend:80/api/', // Changes it when changing server
  cache: new InMemoryCache(),
});

export default client;
