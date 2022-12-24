import { IsArray, IsString } from "class-validator";

export class CreateProductDto {
  @IsString()
  name?: string

  @IsArray()
  gallery?: string[]

  @IsString()
  description?: string;

  @IsArray()
  tags?: string[]

  @IsString()
  projectId?: string[]
}