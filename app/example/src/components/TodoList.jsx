import { VStack, Box, Text, HStack, IconButton, Checkbox } from "@chakra-ui/react";
import { StarIcon, DeleteIcon } from "@chakra-ui/icons";

export default function TodoList({ todos, onToggle, onDelete, onFavorite }) {
  return (
    <VStack spacing={4} mt={4} align="stretch">
      {todos.map((todo) => (
        <HStack
          key={todo.id}
          p={3}
          borderRadius="lg"
          boxShadow="sm"
          bg="gray.50"
          justify="space-between"
        >
          <HStack spacing={3}>
            <Checkbox
              isChecked={todo.status === "done"}
              onChange={() => onToggle(todo)}
            />
            <Text
              textDecoration={todo.status === "done" ? "line-through" : "none"}
              color={todo.status === "done" ? "gray.500" : "gray.800"}
            >
              {todo.task}
            </Text>
          </HStack>

          <HStack spacing={2}>
            <IconButton
              icon={<StarIcon />}
              aria-label="Favoritar"
              onClick={() => onFavorite(todo)}
              variant={todo.favorite ? "solid" : "ghost"}
              colorScheme="yellow"
              size="sm"
            />
            <IconButton
              icon={<DeleteIcon />}
              aria-label="Deletar"
              onClick={() => onDelete(todo)}
              variant="ghost"
              colorScheme="red"
              size="sm"
            />
          </HStack>
        </HStack>
      ))}
    </VStack>
  );
}
