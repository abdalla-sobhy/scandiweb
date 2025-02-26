import { ApolloClient, InMemoryCache } from '@apollo/client';
const client = new ApolloClient({
  uri: 'http://18.205.22.15:8000/api/graphql.php', // Changes it when changing server
  cache: new InMemoryCache(),
});

export default client;
