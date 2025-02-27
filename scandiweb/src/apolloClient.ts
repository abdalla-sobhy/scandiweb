import { ApolloClient, InMemoryCache } from '@apollo/client';

const client = new ApolloClient({
  uri: 'http://54.166.42.245/api/',
  cache: new InMemoryCache(),
});

export default client;

