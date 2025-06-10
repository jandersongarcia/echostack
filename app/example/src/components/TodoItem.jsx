import { Checkbox, IconButton, Stack, Text } from "@chakra-ui/react";
import { StarIcon, DeleteIcon } from "@chakra-ui/icons";

export default function TodoItem({ todo, onToggle, onDelete, onFavorite }) {
  return (
    <Stack direction="row" align="center" justify="space-between">
      <Checkbox
        isChecked={todo.status === 'done'}
        onChange={() => onToggle(todo)}
      >
        <Text as={todo.status === 'done' ? "del" : ""}>{todo.task}</Text>
      </Checkbox>
      <Stack direction="row">
        <IconButton
          icon={<StarIcon />}
          colorScheme={todo.favorite ? "yellow" : "gray"}
          onClick={() => onFavorite(todo)}
        />
        <IconButton icon={<DeleteIcon />} onClick={() => onDelete(todo)} />
      </Stack>
    </Stack>
  );
}
