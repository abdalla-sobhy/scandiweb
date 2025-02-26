import { ApolloClient, InMemoryCache, HttpLink } from '@apollo/client';
const client = new ApolloClient({
  link: new HttpLink({
    uri: 'http://52.87.237.144/api/', // ✅ Use Public API URL
    fetchOptions: { method: 'POST' }, // ✅ Force POST
  }),
  cache: new InMemoryCache(),
});

export default client;
