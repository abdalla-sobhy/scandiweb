import { ApolloClient, InMemoryCache } from "@apollo/client";

const client = new ApolloClient({
  uri: "http://localhost:80/scandiweb_test/api/public/index.php",
  cache: new InMemoryCache(),
});

export default client;
