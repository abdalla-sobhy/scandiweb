import { ApolloClient, InMemoryCache } from '@apollo/client';
const client = new ApolloClient({
  uri: 'http://localhost/api/', // Changes it when changing server
  cache: new InMemoryCache(),
});

export default client;
