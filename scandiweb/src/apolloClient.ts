import { ApolloClient, InMemoryCache } from '@apollo/client';
const client = new ApolloClient({
  uri: 'http://52.87.237.144/api/', // Changes it when changing server
  cache: new InMemoryCache(),
});

export default client;
