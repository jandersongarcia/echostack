import { ChakraProvider } from "@chakra-ui/react";
import TodoPage from "./pages/TodoPage";

function App() {
  return (
    <ChakraProvider>
      <TodoPage />
    </ChakraProvider>
  );
}

export default App;
